<?php declare(strict_types=1);

use Behat\Gherkin\Node\FeatureNode;
use Xentral\LaravelTesting\Behat\BehatDumper;
use Xentral\LaravelTesting\Tests\Unit\Behat\Fixtures\BehatTestDatasets;

test('can dump feature', function (string $filePath, FeatureNode $feature) {
    $result = BehatDumper::dumpFeature($feature);
    expect(trim($result))->toBe(trim(file_get_contents($filePath)));
})->with(BehatTestDatasets::provideBehatFixtures());
