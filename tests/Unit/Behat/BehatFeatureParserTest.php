<?php declare(strict_types=1);

use Behat\Gherkin\Node\FeatureNode;
use Xentral\LaravelTesting\Behat\BehatFeatureParser;
use Xentral\LaravelTesting\Tests\Unit\Behat\Fixtures\BehatTestDatasets;

test('it parses behat feature files properly', function (string $filePath, FeatureNode $feature) {
    $result = BehatFeatureParser::parseFile($filePath);
    expect($result)->toBeInstanceOf(FeatureNode::class)
        ->and($result->getTitle())->toEqual($feature->getTitle())
        ->and($result->getDescription())->toEqual($feature->getDescription())
        ->and($result->getBackground())->toEqual($feature->getBackground())
        ->and($result->getTags())->toEqual($feature->getTags());

    foreach ($result->getScenarios() as $i1 => $scenario) {
        expect($scenario->getTitle())->toEqual($feature->getScenarios()[$i1]->getTitle())
            ->and($scenario->getTags())->toEqual($feature->getScenarios()[$i1]->getTags())
            ->and($scenario->getKeyword())->toEqual($feature->getScenarios()[$i1]->getKeyword());
        foreach ($scenario->getSteps() as $i2 => $step) {
            expect($step->getText())->toEqual($feature->getScenarios()[$i1]->getSteps()[$i2]->getText())
                ->and($step->getKeyword())->toEqual($feature->getScenarios()[$i1]->getSteps()[$i2]->getKeyword());
        }
    }
})->with(BehatTestDatasets::provideBehatFixtures());
