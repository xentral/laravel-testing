<?php

declare(strict_types=1);

namespace Xentral\LaravelTesting\Behat\Exceptions;

use PHPUnit\Framework\AssertionFailedError;

/**
 * Allows wrapping an exception as an expectation failure.
 *
 * @see AssertionFailedError
 */
class AssertionFailedWrappedError extends AssertionFailedError
{
    protected \Throwable $wrapped;

    public function __construct(\Throwable $wrapped)
    {
        parent::__construct($wrapped->getMessage(), $wrapped->getCode(), $wrapped->getPrevious());
        $this->wrapped = $wrapped;
    }

    public function __toString(): string
    {
        $string = $this->wrapped->getMessage();

        if ($trace = $this->wrapped->getTraceAsString()) {
            $string .= "\n".$trace;
        }

        return $string;
    }
}
