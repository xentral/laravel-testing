<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\OpenApi;

use Illuminate\Support\Str;
use Kirschbaum\OpenApiValidator\Exceptions\UnknownSpecFileTypeException;
use Kirschbaum\OpenApiValidator\ValidatesOpenApiSpec as ValidatesOpenApiSpecBase;
use League\OpenAPIValidation\PSR7\Exception\Validation\AddressValidationFailed;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use PHPUnit\Framework\TestCase as PHPunit;
use Xentral\LaravelTesting\OpenApi\Attributes\SchemaFile;
use Xentral\LaravelTesting\Utils;

trait ValidatesOpenApiSpec
{
    use ValidatesOpenApiSpecBase;

    protected ?string $schemaFilePath = null;

    public function schemaFilePath(string $schemaFilePath): self
    {
        $this->schemaFilePath = $schemaFilePath;

        return $this;
    }

    protected function getOpenApiSpecPath(): string
    {
        if ($this->schemaFilePath) {
            $schemaFilePath = $this->schemaFilePath;
            if (! str_starts_with($schemaFilePath, '/')) {
                $schemaFilePath = config('testing.openapi.schema_base_path').'/'.$schemaFilePath;
            }

            return $schemaFilePath;
        }
        $schemaFile = Utils::getAttribute(static::class, SchemaFile::class);
        if (! $schemaFile instanceof SchemaFile) {
            return config('testing.openapi.default_schema');
        }
        $schemaFilePath = $schemaFile->filePath;
        if (! str_starts_with($schemaFilePath, '/')) {
            $schemaFilePath = config('testing.openapi.schema_base_path').'/'.$schemaFilePath;
        }

        return $schemaFilePath;
    }

    protected function getSpecFileType(): string
    {
        $type = strtolower(Str::afterLast($this->getOpenApiSpecPath(), '.'));
        if ($type === 'yml') {
            $type = 'yaml';
        }
        if (! $type || ! in_array($type, ['json', 'yaml'])) {
            throw new UnknownSpecFileTypeException("Expected json or yaml type OpenAPI spec, got {$type}");
        }

        return $type;
    }

    private function handleAddressValidationFailed(AddressValidationFailed $exception, $content = null): void
    {
        $previous = $exception->getPrevious();

        $messages = [];
        if ($previous && $previous instanceof KeywordMismatch) {
            $messages[] = json_encode(is_string($content) ? json_decode($content) : $content, JSON_PRETTY_PRINT);
            $messages[] = $previous->getMessage();
            $messages[] = 'Key: '.implode(' -> ', $previous->dataBreadCrumb()->buildChain());
        }
        $messages[] = $exception->getMessage();
        PHPUnit::fail(implode(PHP_EOL, $messages));
    }
}
