<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat;

use PHPUnitBehat\TestTraits\BehatProvidingTrait;

class BehatFeatureProvider
{
    use BehatProvidingTrait;

    public static function provideFeatureFromFile(string $filePath): array
    {
        return static::provideFeatureFromString(file_get_contents($filePath));
    }

    public static function provideFeatureFromString(string $feature): array
    {
        $feature = BehatFeatureParser::parse($feature);

        return static::provideBehatFeature($feature);
    }
}
