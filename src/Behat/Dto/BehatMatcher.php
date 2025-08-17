<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Dto;

use Behat\Step\Definition;
use Xentral\LaravelTesting\Behat\Attributes\Example;

readonly class BehatMatcher
{
    public function __construct(
        public string $keyword,
        public Definition $definition,
        /** @var Example[] */
        public array $examples,
        public string $className,
        public string $methodName,
        public string $filePath,
    ) {}
}
