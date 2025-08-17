<?php declare(strict_types=1);

namespace Xentral\LaravelTesting;

use Illuminate\Support\ServiceProvider;

class TestingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/testing.php', 'testing');
    }

    public function register(): void {}
}
