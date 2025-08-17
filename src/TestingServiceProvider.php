<?php declare(strict_types=1);

namespace Xentral\LaravelTesting;

use Illuminate\Support\ServiceProvider;

class TestingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/testing.php', 'testing');
        $this->publishes([
            dirname(__DIR__).'/.ai/guidelines/xentral-testing.blade.php' => base_path('.ai/guidelines/xentral-testing.blade.php'),
        ], 'xentral-testing');
    }

    public function register(): void {}
}
