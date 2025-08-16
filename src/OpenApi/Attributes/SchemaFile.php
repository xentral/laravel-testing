<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\OpenApi\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class SchemaFile
{
    public function __construct(public string $filePath) {}
}
