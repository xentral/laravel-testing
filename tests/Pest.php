<?php declare(strict_types=1);

use Orchestra\Testbench\Pest\WithPest;
use Xentral\LaravelTesting\Tests\TestCase;

uses(TestCase::class, WithPest::class)->in(__DIR__.'/Feature');
