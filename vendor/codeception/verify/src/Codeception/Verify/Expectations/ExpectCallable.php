<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Asserts\AssertThrows;
use Codeception\Verify\Expect;
use Exception;
use Throwable;

class ExpectCallable extends Expect
{
    use AssertThrows;

    public function __construct(callable $callable)
    {
        parent::__construct($callable);
    }

    /**
     * @param Exception|string|null $throws
     * @param string|false $message
     * @return $this
     */
    public function notToThrow($throws = null, $message = false): self
    {
        return $this->assertDoesNotThrow($throws, $message);
    }

    /**
     * @param Exception|string|null $throws
     * @param string|false $message
     * @return ExpectCallable
     * @throws Throwable
     */
    public function toThrow($throws = null, $message = false): self
    {
        return $this->assertThrows($throws, $message);
    }
}