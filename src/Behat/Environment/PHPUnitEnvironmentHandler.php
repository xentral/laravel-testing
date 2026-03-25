<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Environment;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Handler\EnvironmentHandler;
use Behat\Testwork\Suite\Suite;

/**
 * Represents environment handler for a PhpUnitEnvironment.
 *
 * Based on \Behat\Testwork\Environment\Handler\StaticEnvironmentHandler.
 */
class PHPUnitEnvironmentHandler implements EnvironmentHandler
{
    public function supportsSuite(Suite $suite): bool
    {
        return true;
    }

    public function buildEnvironment(Suite $suite): PHPUnitEnvironment
    {
        return new PHPUnitEnvironment($suite);
    }

    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null): bool
    {
        return $environment instanceof PHPUnitEnvironment;
    }

    public function isolateEnvironment(Environment $environment, $testSubject = null): Environment
    {
        return $environment;
    }
}
