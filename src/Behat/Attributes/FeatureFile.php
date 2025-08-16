<?php
declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class FeatureFile
{
    public function __construct(public string $filePath) {}
}
