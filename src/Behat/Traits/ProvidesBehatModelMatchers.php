<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use RuntimeException;

trait ProvidesBehatModelMatchers
{
    protected function getModelFactory(string $model): Factory
    {
        return match ($model) {
            default => $this->guessModelFactory($model),
        };
    }

    protected function guessModelFactory(string $model): Factory
    {
        // Convert to singular if the model name is plural
        $singular = Str::singular($model);

        // Ensure proper capitalization
        $singular = Str::studly($singular);

        // Try different common namespaces
        $possibleNamespaces = [
            'App\\Models\\',
        ];

        foreach ($possibleNamespaces as $namespace) {
            $modelClass = $namespace.$singular;
            if (class_exists($modelClass)) {
                return $modelClass::factory();
            }
        }

        throw new RuntimeException("Model factory not found for model: {$model}");
    }

    protected function prepareModelProperties(string $model, array $data): array
    {
        return match ($model) {
            default => $data,
        };
    }

    #[Given('/^There are the following ([^\s]+)$/')]
    public function thereIsTheFollowingModelIsSeeded(string $model, TableNode $table): void
    {
        $this->getModelFactory($model)->createMany($this->prepareModelProperties($model, $this->parseTable($table)));
    }

    #[Given('/^There are ([^\s]+) ([^\s]+)$/')]
    public function thereAreCountModels(int $count, string $entity): void
    {
        $this->getModelFactory($entity)->count($count)->create();
    }
}
