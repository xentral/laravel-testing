<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Xentral\LaravelTesting\TestingServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Xentral\\LaravelTesting\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function setUpDatabase($app): void
    {
    }

    protected function getPackageProviders($app): array
    {
        return [
            TestingServiceProvider::class,
        ];
    }
}
