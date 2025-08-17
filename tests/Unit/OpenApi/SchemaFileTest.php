<?php declare(strict_types=1);

use Xentral\LaravelTesting\OpenApi\Attributes\SchemaFile;
use Xentral\LaravelTesting\Utils;

it('can be instantiated with file path', function () {
    $filePath = '/path/to/schema.json';
    $schemaFile = new SchemaFile($filePath);

    expect($schemaFile->filePath)->toBe($filePath);
});

it('can be used as attribute on classes', function () {
    $attribute = Utils::getAttribute(TestClassWithSchemaFile::class, SchemaFile::class);
    expect($attribute->filePath)->toBe('/path/to/test-schema.json');
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(SchemaFile::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

#[SchemaFile('/path/to/test-schema.json')]
class TestClassWithSchemaFile {}
