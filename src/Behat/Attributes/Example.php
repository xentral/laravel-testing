<?php
declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
readonly class Example
{
    public function __construct(public string $stepText, public array $matches = [], public array $data = []) {}
}
