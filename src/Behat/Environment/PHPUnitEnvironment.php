<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Environment;

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Suite\Suite;
use PHPUnit\Framework\TestCase;

/**
 * A Behat environment representing a PHPUnit testcase.
 *
 * This implements ContextEnvironment so as to avoid the need for a new
 * EnvironmentReader class as well; this is somewhat hackish as this interface
 * requires more methods than we strictly need for the phpunit use case.
 *
 * Much of the code is borrowed from
 * Behat\Behat\Context\Environment\UnitializedContextEnvironment and
 * Behat\Testwork\Environment\StaticEnvironment.
 *
 * When used with a PHPUnit testcase, the testcase class name is supplied using
 * ::registerContextClass(), and this will be used by Behat to parse the testcase
 * using reflection to find methods with Behat annotations matching step definitions.
 * The instantiated testclass is supplied with ::setTestCase() and this is used in
 * ::bindCallee to execute the found step method on the instantiated testcase object.
 */
class PHPUnitEnvironment implements ContextEnvironment
{
    /**
     * @var array[]
     */
    protected $contextClasses = [];

    /**
     * @var Suite
     */
    protected $suite;

    /**
     * @var TestCase
     */
    protected $testCase;

    public function __construct(Suite $suite)
    {
        $this->suite = $suite;
    }

    /**
     * Specifies the current PhpUnit test case.
     */
    public function setTestCase(TestCase $testCase): void
    {
        $this->testCase = $testCase;
    }

    /**
     * {@inheritdoc}
     */
    public function bindCallee(Callee $callee)
    {
        // Execute the method against the instantiated testcase.
        $method = $callee->getCallable()[1];

        return [$this->testCase, $method];
    }

    /**
     * Registers context class.
     *
     * @param  string  $contextClass
     *
     * @throws ContextNotFoundException If class does not exist
     */
    public function registerContextClass($contextClass, ?array $arguments = null): void
    {
        if (! class_exists($contextClass)) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context class not found and can not be used.',
                $contextClass
            ), $contextClass);
        }
        $this->contextClasses[$contextClass] = $arguments ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasContexts(): bool
    {
        return count($this->contextClasses) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getContextClasses(): array
    {
        return array_keys($this->contextClasses);
    }

    /**
     * {@inheritdoc}
     */
    public function hasContextClass($class): bool
    {
        return isset($this->contextClasses[$class]);
    }

    /**
     * Returns context classes with their arguments.
     *
     * @return array[]
     */
    public function getContextClassesWithArguments(): array
    {
        return $this->contextClasses;
    }

    /**
     * {@inheritdoc}
     */
    final public function getSuite(): Suite
    {
        return $this->suite;
    }
}
