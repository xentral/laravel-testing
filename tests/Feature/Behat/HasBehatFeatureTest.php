<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Tests\Feature\Behat;

use PHPUnit\Framework\Attributes\DataProvider;
use Workbench\App\Models\TestModel;
use Xentral\LaravelTesting\Behat\Traits\HasBehatFeature;
use Xentral\LaravelTesting\Tests\TestCase;

class HasBehatFeatureTest extends TestCase
{
    use HasBehatFeature;

    #[DataProvider('featureProvider')]
    public function test_behat_scenario($scenario, $feature)
    {
        $this->withoutValidation();
        $this->executeScenario($scenario, $feature);
    }

    public function getTestModelFactory()
    {
        return TestModel::factory();
    }
}
