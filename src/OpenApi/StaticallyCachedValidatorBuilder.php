<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\OpenApi;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

/**
 * ValidatorBuilder re-parses the OpenAPI spec on every getRequestValidator() /
 * getResponseValidator() call unless a PSR-6 cache is wired up, and the
 * kirschbaum trait creates a fresh builder per test method - so every test
 * paid the full parse (seconds per call for a large spec) twice per HTTP
 * request. See https://github.com/kirschbaum-development/laravel-openapi-validator/issues/14
 *
 * Parsing a spec file is deterministic within one PHP process, so the parsed
 * schema is memoized statically per spec path instead. The schema object is
 * only ever read by the validators, which makes sharing it safe.
 */
final class StaticallyCachedValidatorBuilder extends ValidatorBuilder
{
    /** @var array<string, OpenApi> */
    private static array $schemas = [];

    public function __construct(private readonly string $specPath) {}

    protected function getOrCreateSchema(): OpenApi
    {
        return self::$schemas[$this->specPath] ??= parent::getOrCreateSchema();
    }
}
