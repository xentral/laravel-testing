<?php declare(strict_types=1);

use Xentral\LaravelTesting\Qase\XentralQaseExtension;

it('has bootstrap method', function () {
    $extension = new XentralQaseExtension;

    expect($extension)->toBeInstanceOf(PHPUnit\Runner\Extension\Extension::class);
});
