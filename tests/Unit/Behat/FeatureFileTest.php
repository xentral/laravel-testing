<?php declare(strict_types=1);

use Xentral\LaravelTesting\Behat\Attributes\FeatureFile;
use Xentral\LaravelTesting\Utils;

it('can be instantiated with file path', function () {
    $filePath = '/path/to/feature.feature';
    $featureFile = new FeatureFile($filePath);

    expect($featureFile->filePath)->toBe($filePath);
});

it('can be used as attribute on classes', function () {
    $attribute = Utils::getAttribute(TestClassWithFeatureFile::class, FeatureFile::class);
    expect($attribute->filePath)->toBe('/path/to/test-feature.feature');
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(FeatureFile::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

#[FeatureFile('/path/to/test-feature.feature')]
class TestClassWithFeatureFile {}
