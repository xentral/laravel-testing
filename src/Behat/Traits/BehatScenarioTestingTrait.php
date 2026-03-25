<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Xentral\LaravelTesting\Behat\Constraints\HasScenarioPassedConstraint;

trait BehatScenarioTestingTrait
{
    use BehatContainerTrait;
    use BehatEnvironmentTrait;
    use BehatStepResultCollectionTrait;

    public static function assertBehatScenarioPassed($scenarioResults, $scenario = null, $stepResults = [], $snippetGenerator = null, $environment = null, $message = '', $callHandler = ''): void
    {
        $constraint = new HasScenarioPassedConstraint($scenario, $stepResults, $callHandler, $snippetGenerator, $environment);
        self::assertThat($scenarioResults, $constraint, $message);
    }

    public function executeBehatScenario($scenario, $feature)
    {
        $tester = $this->getBehatContainer()->get(TesterExtension::SCENARIO_TESTER_ID);

        return $tester->test($this->getBehatEnvironment(), $feature, $scenario, false);
    }

    public function assertBehatScenario($scenario, $feature): void
    {
        $this->startBehatStepResultCollection();
        $snippetGenerator = $this->getBehatContainer()->get(ContextExtension::CONTEXT_SNIPPET_GENERATOR_ID);
        $scenarioResults = $this->executeBehatScenario($scenario, $feature);
        $this->assertBehatScenarioPassed($scenarioResults, $scenario, $this->getBehatStepResults(), $snippetGenerator, $this->getBehatEnvironment());
    }
}
