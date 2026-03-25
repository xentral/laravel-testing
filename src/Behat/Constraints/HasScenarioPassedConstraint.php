<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Constraints;

use Behat\Behat\Context\Snippet\Generator\FixedContextIdentifier;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\Call\Handler\RuntimeCallHandler;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Exception;
use PHPUnit\Framework\Constraint\Constraint;
use Xentral\LaravelTesting\Behat\Environment\PHPUnitEnvironment;

class HasScenarioPassedConstraint extends Constraint
{
    /**
     * @var string
     */
    protected $scenarioCallHandler;

    /**
     * @var string
     */
    protected $snippetTemplate = <<<'TPL'
      /**
       * @%%s %s
       */
      public function %s(%s) {

      }
    TPL;

    /**
     * @param  ScenarioNode|null  $scenario
     * @param  array  $stepResults
     * @param  string  $scenarioCallHandler
     * @param  PHPUnitEnvironment  $environment
     * @param  mixed  $snippetGenerator
     */
    public function __construct(protected $scenario = null, protected $stepResults = [], $scenarioCallHandler = '', protected $snippetGenerator = null, protected $environment = null)
    {
        $this->scenarioCallHandler = empty($scenarioCallHandler) ? RuntimeCallHandler::class : $scenarioCallHandler;
    }

    public function toString(): string
    {
        return 'scenario passed';
    }

    protected function failureDescription(mixed $other): string
    {
        // Because we throw exceptions in ::bubbleStepResults(),
        // this is only used for undefined steps, not failing steps.
        return ' '.$this->toString();
    }

    protected function additionalFailureDescription(mixed $other): string
    {
        // Because we throw exceptions in ::bubbleStepResults(),
        // we expect to only use this for undefined steps, not failing steps.
        $stepsMessage = $this->stepResultsMessage($this->stepResults);
        $snippetsMessage = $this->snippetsMessage();

        return "$stepsMessage\n\n$snippetsMessage";
    }

    protected function matches(mixed $other): bool
    {
        $this->bubbleStepResults();

        return $other->isPassed();
    }

    /**
     * Force exceptions and stdout from steps to bubble up into phpunit.
     *
     * Behat's RuntimeCallHandler catches these during step execution
     * and stores them on the call result.
     */
    protected function bubbleStepResults(): void
    {
        $stepsSoFar = [];
        foreach ($this->stepResults as $stepResult) {
            $stepsSoFar[] = $stepResult;
            $result = $stepResult['testResult'];
            if ($result instanceof ExecutedStepResult && $result->getCallResult()->hasStdOut()) {
                print_r($result->getCallResult()->getStdOut());
            }
            if ($result instanceof ExceptionResult && $exception = $result->getException()) {
                // Modify the exception to truncate the trace
                $this->truncateExceptionTrace($exception, $this->scenarioCallHandler);
                $this->modifyExceptionMessage($exception, $stepsSoFar);
                throw $exception;
            }
        }
    }

    /**
     * Adds details about the scenario steps so far to a step exception message.
     */
    protected function modifyExceptionMessage(\Throwable $exception, array $stepsSoFar): void
    {
        $traceReflector = new \ReflectionProperty('Exception', 'message');
        $originalMessage = $traceReflector->getValue($exception);
        $stepResultsMessage = $this->stepResultsMessage($stepsSoFar);
        $modifiedMessage = "\n$stepResultsMessage\n\n$originalMessage";
        $traceReflector->setValue($exception, $modifiedMessage);
    }

    /**
     * A list of steps executed in a scenario.
     */
    protected function stepResultsMessage(array $stepResults): string
    {
        $converter = new ResultToStringConverter;
        $intro = ! is_null($this->scenario) ? "Scenario '".$this->scenario->getTitle()."' had steps:" : '"Steps:"';
        $steps = [];
        foreach ($this->stepResults as $stepResult) {
            $result = $stepResult['testResult'];
            $step = $stepResult['step'];
            $resultString = ucfirst($converter->convertResultToString($result));
            $stepString = $step->getKeyword().' '.$step->getText();
            $steps[] = $resultString.': '.$stepString;
        }

        return "$intro\n".implode("\n", $steps);
    }

    /**
     * Truncate an exception trace at a certain ceiling.
     */
    protected function truncateExceptionTrace(\Throwable $exception, string $ceiling): void
    {
        $traceReflector = new \ReflectionProperty('Exception', 'trace');
        $fullTrace = $exception->getTrace();
        $trace = $this->truncateTraceArray($fullTrace, $ceiling);
        $traceReflector->setValue($exception, $trace);
    }

    /**
     * Truncate a trace array at a certain ceiling.
     */
    protected function truncateTraceArray(array $trace, string $ceiling): array
    {
        if (empty($ceiling)) {
            return $trace;
        }
        $position = 0;
        foreach ($trace as $layer => $call) {
            $class = $call['class'] ?? '';
            $position = $layer;
            if ($class === $ceiling) {
                break;
            }
        }

        return array_slice($trace, 0, $position - 1);
    }

    protected function snippetsMessage(): string
    {
        $this->setupSnippetGenerator();
        $snippets = [];
        foreach ($this->stepResults as $stepResult) {
            $result = $stepResult['testResult'];
            $step = $stepResult['step'];
            if ($result->getResultCode() == StepResult::UNDEFINED) {
                $snippets[] = $this->generateSnippet($step);
            }
        }

        return "\nYou can define these undefined steps in your PHPUnit test class like this:\n\n".implode("\n\n", $snippets);
    }

    protected function setupSnippetGenerator(): void
    {
        // Set the context on the snippet generator.
        $context = $this->environment->getContextClasses()[0];
        $identifier = new FixedContextIdentifier($context);
        $this->snippetGenerator->setContextIdentifier($identifier);

        // Determine the correct property name.
        $reflector = new \ReflectionClass($this->snippetGenerator::class);
        $templateProperty = null;
        if ($reflector->hasProperty('snippetTemplate')) {
            $templateProperty = 'snippetTemplate';
        } elseif ($reflector->hasProperty('templateTemplate')) {
            $templateProperty = 'templateTemplate';
        }

        if ($templateProperty !== null) {
            // Modify the snippet generator's template to remove reference to pending exception.
            $templateReflector = new \ReflectionProperty($this->snippetGenerator::class, $templateProperty);
            $templateReflector->setValue($this->snippetGenerator, $this->snippetTemplate);
        }
    }

    protected function generateSnippet($step): ?string
    {
        if (! is_null($this->snippetGenerator) && ! is_null($this->environment)) {
            return $this->snippetGenerator->generateSnippet($this->environment, $step)->getSnippet();
        }

        return null;
    }
}
