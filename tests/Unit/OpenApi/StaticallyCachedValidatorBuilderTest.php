<?php declare(strict_types=1);

use Xentral\LaravelTesting\OpenApi\StaticallyCachedValidatorBuilder;

function buildSchemaFor(string $specPath): object
{
    $builder = (new StaticallyCachedValidatorBuilder($specPath))->fromJsonFile($specPath);
    $method = new ReflectionMethod(StaticallyCachedValidatorBuilder::class, 'getOrCreateSchema');

    return $method->invoke($builder);
}

it('parses the spec only once per path and reuses the schema across builder instances', function () {
    $specPath = dirname(__DIR__, 2).'/schemas/test-models.json';

    $first = buildSchemaFor($specPath);
    $second = buildSchemaFor($specPath);

    expect($second)->toBe($first);
});

it('keeps separate schemas per spec path', function () {
    $specPath = dirname(__DIR__, 2).'/schemas/test-models.json';
    $otherPath = sys_get_temp_dir().'/other-spec-'.getmypid().'.json';
    copy($specPath, $otherPath);

    try {
        expect(buildSchemaFor($otherPath))->not->toBe(buildSchemaFor($specPath));
    } finally {
        unlink($otherPath);
    }
});

it('still produces working validators from the shared schema', function () {
    $specPath = dirname(__DIR__, 2).'/schemas/test-models.json';
    $builder = (new StaticallyCachedValidatorBuilder($specPath))->fromJsonFile($specPath);

    expect($builder->getRequestValidator())->not->toBeNull()
        ->and($builder->getResponseValidator())->not->toBeNull();
});
