<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Traits;

use Behat\Testwork\Suite\GenericSuite;
use Xentral\LaravelTesting\Behat\Environment\PHPUnitEnvironment;

trait BehatEnvironmentTrait
{
    /**
     * @var \Xentral\LaravelTesting\Behat\Environment\PHPUnitEnvironment
     */
    protected $behatEnvironment;

    /**
     * Get a Behat environment suitable for PHP unit.
     */
    public function getBehatEnvironment(): PHPUnitEnvironment
    {
        if (is_null($this->behatEnvironment)) {
            $environment = new PHPUnitEnvironment(new GenericSuite('test', []));
            $environment->registerContextClass($this::class);
            $environment->setTestCase($this);
            $this->behatEnvironment = $environment;
        }

        return $this->behatEnvironment;
    }
}
