<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Tests\Unit\Behat\Fixtures;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;

/**
 * Shared test datasets for Behat-related tests
 */
class BehatTestDatasets
{
    public static function provideBehatFixtures(): array
    {
        return [
            'simple feature' => [
                'filePath' => __DIR__.'/simple-feature.feature',
                'feature' => self::makeFeature('Simple feature', [
                    self::makeScenario('Simple scenario', [
                        new StepNode('Given', 'I have a test environment', [], 2, null),
                        new StepNode('When', 'I execute a simple test', [], 3, null),
                        new StepNode('Then', 'it should pass', [], 4, null),
                    ])]),
            ],
        ];
    }

    private static function makeFeature(string $title, array $scenarios): FeatureNode
    {
        return new FeatureNode(
            $title,
            null,
            [],
            null,
            $scenarios,
            '',
            'en',
            null,
            1
        );
    }

    private static function makeScenario(string $title, array $steps, array $tags = []): ScenarioNode
    {
        return new ScenarioNode(
            $title,
            $tags,
            $steps,
            'Scenario',
            1
        );
    }
}
