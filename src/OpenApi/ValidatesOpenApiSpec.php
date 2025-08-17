<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\OpenApi;

use Illuminate\Support\Str;
use Kirschbaum\OpenApiValidator\Exceptions\UnknownSpecFileTypeException;
use Kirschbaum\OpenApiValidator\ValidatesOpenApiSpec as ValidatesOpenApiSpecBase;
use League\OpenAPIValidation\PSR7\Exception\Validation\AddressValidationFailed;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use PHPUnit\Framework\TestCase as PHPunit;
use Xentral\LaravelTesting\OpenApi\Attributes\SchemaFile;
use Xentral\LaravelTesting\Utils;

trait ValidatesOpenApiSpec
{
    use ValidatesOpenApiSpecBase;

    protected function getOpenApiSpecPath(): string
    {
        $schemaFile = Utils::getAttribute(static::class, SchemaFile::class);
        if (! $schemaFile instanceof SchemaFile) {
            throw new \RuntimeException('No schema file found. Please add a SchemaFile attribute on your test class.');
        }
        $schemaFilePath = $schemaFile->filePath;
        if (! str_starts_with($schemaFilePath, '/')) {
            $schemaFilePath = base_path($schemaFilePath);
        }
        if (! file_exists($schemaFilePath)) {
            throw new \RuntimeException("Schema file not found: {$schemaFilePath}");
        }

        return $schemaFilePath;
    }

    protected function getSpecFileType(): string
    {
        $type = strtolower(Str::afterLast($this->getOpenApiSpecPath(), '.'));
        if ($type === 'yml') {
            $type = 'yaml';
        }
        if (! $type || ! in_array($type, ['json', 'yaml'])) {
            throw new UnknownSpecFileTypeException("Expected json or yaml type OpenAPI spec, got {$type}");
        }

        return $type;
    }

    private function handleAddressValidationFailed(AddressValidationFailed $exception, $content = null): void
    {
        $previous = $exception->getPrevious();

        $messages = [];
        if ($previous && $previous instanceof KeywordMismatch) {
            $messages[] = json_encode(is_string($content) ? json_decode($content) : $content, JSON_PRETTY_PRINT);
            $messages[] = $previous->getMessage();
            $messages[] = 'Key: '.implode(' -> ', $previous->dataBreadCrumb()->buildChain());
        }
        $messages[] = $exception->getMessage();
        PHPUnit::fail(implode(PHP_EOL, $messages));
    }
}
