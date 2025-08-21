<?php declare(strict_types=1);

namespace Xentral\LaravelTesting\Tests\Feature\Behat;

use Illuminate\Database\Eloquent\Factories\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use Qase\PHPUnitReporter\Attributes\Suite;
use Workbench\App\Models\TestModel;
use Xentral\LaravelTesting\Behat\Traits\HasBehatFeature;
use Xentral\LaravelTesting\Tests\TestCase;

#[Suite('HasBehatFeatureTest')]
class HasBehatFeatureTest extends TestCase
{
    use HasBehatFeature;

    #[DataProvider('featureProvider')]
    public function test_behat_scenario($scenario, $feature)
    {
        $this->withoutValidation();
        $this->executeScenario($scenario, $feature);
    }

    public function getTestModelFactory(): Factory
    {
        return TestModel::factory();
    }
}
