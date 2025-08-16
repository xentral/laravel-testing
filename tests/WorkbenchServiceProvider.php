<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Tests;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        config()->set('cache.default', 'array');
    }
}
