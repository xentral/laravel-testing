<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Tests\Unit\Behat\Fixtures;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;

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
                        self::makeStep('Given', 'I have a test environment'),
                        self::makeStep('When', 'I execute a simple test'),
                        self::makeStep('Then', 'it should pass'),
                    ])]),
            ],
            'tagged feature' => [
                'filePath' => __DIR__.'/tagged-feature.feature',
                'feature' => self::makeFeature('Tagged feature', [
                    self::makeScenario('Tagged scenario', [
                        self::makeStep('Given', 'I have a tagged test'),
                        self::makeStep('When', 'I run the test'),
                        self::makeStep('Then', 'the tags should be preserved'),
                    ], ['tag1', 'tag2'])]),
            ],
            'feature with table data' => [
                'filePath' => __DIR__.'/table-feature.feature',
                'feature' => self::makeFeature('Feature with table data', [
                    self::makeScenario('Scenario with table', [
                        self::makeStep('Given', 'I have users with data', [
                            new TableNode([
                                ['Name', 'Age'],
                                ['John', '30'],
                                ['Jane', '25'],
                            ]),
                        ]),
                        self::makeStep('When', 'I process the data'),
                        self::makeStep('Then', 'all users should be created'),
                    ])]),
            ],
            'feature with pystring data' => [
                'filePath' => __DIR__.'/pystring-feature.feature',
                'feature' => self::makeFeature('Feature with JSON data', [
                    self::makeScenario('Scenario with JSON data', [
                        self::makeStep('Given', 'I have JSON data', [
                            new PyStringNode(['{"key": "value", "number": 42}'], 2),
                        ]),
                        self::makeStep('When', 'I parse the JSON'),
                        self::makeStep('Then', 'it should be valid'),
                    ])]),
            ],
            'feature with and keywords' => [
                'filePath' => __DIR__.'/and-keywords-feature.feature',
                'feature' => self::makeFeature('Feature with multiple steps', [
                    self::makeScenario('Multiple Given steps', [
                        self::makeStep('Given', 'I have first condition'),
                        self::makeStep('And', 'I have second condition'),
                        self::makeStep('When', 'I perform an action'),
                        self::makeStep('Then', 'I get first result'),
                        self::makeStep('And', 'I get second result', keywordType: 'Then'),
                    ])]),
            ],
            'feature with multiple scenarios' => [
                'filePath' => __DIR__.'/multi-scenario-feature.feature',
                'feature' => self::makeFeature('Feature with multiple scenarios', [
                    self::makeScenario('First scenario', [
                        self::makeStep('Given', 'I have first setup'),
                        self::makeStep('When', 'I do first action'),
                        self::makeStep('Then', 'I get first result'),
                    ]),
                    self::makeScenario('Second scenario', [
                        self::makeStep('Given', 'I have second setup'),
                        self::makeStep('When', 'I do second action'),
                        self::makeStep('Then', 'I get second result'),
                    ])]),
            ],
        ];
    }

    public static function makeFeature(string $title, array $scenarios): FeatureNode
    {
        return new FeatureNode(
            $title,
            null,
            [],
            null,
            $scenarios,
            'Feature',
            'en',
            null,
            1
        );
    }

    public static function makeScenario(string $title, array $steps, array $tags = []): ScenarioNode
    {
        return new ScenarioNode(
            $title,
            $tags,
            $steps,
            'Scenario',
            1
        );
    }

    public static function makeStep(string $keyword, string $text, array $arguments = [], int $line = 1, ?string $keywordType = null): StepNode
    {
        return new StepNode($keyword, $text, $arguments, $line, $keywordType ?? $keyword);
    }
}
