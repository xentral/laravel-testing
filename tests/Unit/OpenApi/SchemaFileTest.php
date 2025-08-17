<?php declare(strict_types=1);

use Xentral\LaravelTesting\OpenApi\Attributes\SchemaFile;

it('can be instantiated with file path', function () {
    $filePath = '/path/to/schema.json';
    $schemaFile = new SchemaFile($filePath);

    expect($schemaFile->filePath)->toBe($filePath);
});

it('can be used as attribute on classes', function () {
    $reflection = new ReflectionClass(TestClassWithSchemaFile::class);
    $attributes = $reflection->getAttributes(SchemaFile::class);

    expect($attributes)->toHaveCount(1);

    $instance = $attributes[0]->newInstance();
    expect($instance->filePath)->toBe('/path/to/test-schema.json');
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(SchemaFile::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

#[SchemaFile('/path/to/test-schema.json')]
class TestClassWithSchemaFile {}
