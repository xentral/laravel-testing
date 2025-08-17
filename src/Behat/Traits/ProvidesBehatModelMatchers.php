<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use RuntimeException;
use Xentral\LaravelTesting\Behat\Attributes\Example;

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
        $singular = Str::studly(Str::singular($model));

        $methodName = 'get'.$singular.'Factory';
        if (method_exists($this, $methodName)) {
            return call_user_func([$this, $methodName]);
        }

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
    #[Example('There are the following users', ['users'], [['name' => 'John Doe', 'email' => 'john@example.com'], ['name' => 'Jane Smith', 'email' => 'jane@example.com']])]
    #[Example('There are the following products', ['products'], [['name' => 'Product A', 'price' => '99.99'], ['name' => 'Product B', 'price' => '149.99']])]
    #[Example('There are the following orders', ['orders'], [['order_number' => 'ORD-001', 'status' => 'pending'], ['order_number' => 'ORD-002', 'status' => 'completed']])]
    public function thereIsTheFollowingModelIsSeeded(string $model, TableNode $table): void
    {
        $this->getModelFactory($model)->createMany($this->prepareModelProperties($model, $this->parseTable($table)));
    }

    #[Given('/^There are ([^\s]+) ([^\s]+)$/')]
    #[Example('There are 5 users', ['5', 'users'])]
    #[Example('There are 10 products', ['10', 'products'])]
    #[Example('There are 3 orders', ['3', 'orders'])]
    #[Example('There are 15 customers', ['15', 'customers'])]
    public function thereAreCountModels(int $count, string $entity): void
    {
        $this->getModelFactory($entity)->count($count)->create();
    }
}
