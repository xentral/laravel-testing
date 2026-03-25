<?php declare(strict_types=1);

use PHPUnit\Runner\Extension\Extension;
use Xentral\LaravelTesting\Qase\XentralQaseExtension;

it('has bootstrap method', function () {
    $extension = new XentralQaseExtension;

    expect($extension)->toBeInstanceOf(Extension::class);
});
