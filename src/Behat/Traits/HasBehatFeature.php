<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use PHPUnitBehat\TestTraits\BehatProvidingTrait;
use PHPUnitBehat\TestTraits\BehatScenarioTestingTrait;
use Xentral\LaravelTesting\Behat\Attributes\FeatureFile;
use Xentral\LaravelTesting\Behat\BehatFeatureParser;
use Xentral\LaravelTesting\Qase\CustomQaseReporter;
use Xentral\LaravelTesting\Utils;

trait HasBehatFeature
{
    use BehatProvidingTrait;
    use BehatScenarioTestingTrait;
    use ProvidesBehatHttpMatchers;
    use ProvidesBehatModelMatchers;

    public static string $feature = '';

    /**
     * Data provider for the behat feature test.
     *
     * It parses the TestSuite attribute or the static $feature property
     * as a Behat feature. Then it breaks it down into scenarios for test.
     */
    public static function featureProvider(): array
    {
        // First we check for the convention of having a .feature file next to the test with the same name
        $ref = new \ReflectionClass(static::class);
        $featureFilePath = str_replace('.php', '.feature', $ref->getFileName());
        if (file_exists($featureFilePath)) {
            return static::provideBehatFeature(BehatFeatureParser::parseFile($featureFilePath));
        }
        // If no .feature file is found, we check for the FeatureFile attribute
        $featureFileAttribute = Utils::getAttribute(static::class, FeatureFile::class);
        if ($featureFileAttribute instanceof FeatureFile) {
            return static::provideBehatFeature(BehatFeatureParser::parseFile($featureFileAttribute->filePath));
        }
        // If no FeatureFile attribute is found, use the static::$feature property.
        // If that is also not available, we bail
        if (empty(static::$feature)) {
            throw new \RuntimeException('No FeatureFile attribute found. Please add one to your test class.');
        }

        // If no FeatureFile attribute is found, use the static::$feature property.
        return static::provideBehatFeature(BehatFeatureParser::parse(static::$feature));
    }

    public function executeScenario($scenario, $feature): void
    {
        CustomQaseReporter::title($scenario->getTitle());
        // Find a cool way to attach the scenario to the test case
        // CustomQaseReporter::attachScenario(BehatDumper::dumpScenario($scenario));
        $this->assertBehatScenario($scenario, $feature);
    }
}
