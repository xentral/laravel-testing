<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Illuminate\Database\Eloquent\Model;

trait EnrichesBehatStepData
{
    /** @var array<string, Model> */
    protected array $rememberedModels = [];

    protected function parseTable(TableNode $node): array
    {
        $rows = $node->getColumnsHash();

        foreach ($rows as $i => $row) {
            foreach ($row as $key => $value) {
                $rows[$i][$key] = $this->replacePlaceholders($value);
            }
        }

        return $rows;
    }

    protected function parsePayload(PyStringNode $node): array
    {
        $raw = $node->getRaw();

        $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

        $resolved = $this->replacePlaceholders($decoded);

        return $resolved;
    }

    protected function replacePlaceholders(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->replacePlaceholders($v);
            }

            return $data;
        }

        if (is_string($data)) {
            // only replace when the whole string is a single placeholder like {{foo.bar}}
            if (preg_match('/^\{\{\s*([a-zA-Z0-9_]+(?:\.[^\}\s]+)*)\s*\}\}$/', $data, $m) === 1) {
                return $this->resolvePlaceholder($m[1]);
            }

            return $data;
        }

        return $data;
    }

    /**
     * Resolves a placeholder like `{{foo.bar}}` to the actual value.
     *
     * @param  string  $expr  The placeholder expression, e.g. "foo.bar".
     * @return mixed The resolved value from the remembered models.
     *
     * @throws \RuntimeException If the placeholder root is unknown or cannot be resolved.
     */
    protected function resolvePlaceholder(string $expr): mixed
    {
        [$root, $path] = \Str::contains($expr, '.') ? explode('.', $expr, 2) : [$expr, null];

        if (! isset($this->rememberedModels[$root])) {
            throw new \RuntimeException(sprintf('Unknown placeholder root "%s".', $root));
        }

        $model = $this->rememberedModels[$root];

        // If no nested path, return the model itself; otherwise, drill down using Laravel's data_get.
        $value = $path === null ? $model : data_get($model, $path);

        if ($value === null) {
            throw new \RuntimeException(sprintf('Could not resolve "%s" from remembered model "%s".', $expr, $root));
        }

        return $value;
    }
}
