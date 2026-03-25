<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use PHPUnit\Framework\Attributes\DataProvider;

trait BehatTestTrait
{
    use BehatProvidingTrait;
    use BehatScenarioTestingTrait;

    /**
     * Data provider for ::testBehatScenario().
     *
     * Parses the ::feature property as a Behat feature,
     * breaking it down into individual scenarios for testing.
     */
    public static function providerTestBehatScenario(): array
    {
        $feature = static::parseBehatFeature(static::$feature);

        return static::provideBehatFeature($feature);
    }

    /**
     * Test a Behat scenario.
     */
    #[DataProvider('providerTestBehatScenario')]
    public function testBehatScenario($scenario, $feature): void
    {
        $this->setProvidedData($scenario, $feature);
        $this->assertBehatScenario($scenario, $feature);
    }
}
