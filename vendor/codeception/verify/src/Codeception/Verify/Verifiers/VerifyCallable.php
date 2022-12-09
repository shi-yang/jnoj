<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Asserts\AssertThrows;
use Codeception\Verify\Verify;
use Exception;
use Throwable;

class VerifyCallable extends Verify
{
    use AssertThrows;

    public function __construct(callable $callable)
    {
        parent::__construct($callable);
    }

    /**
     * @param Exception|string|null $throws
     * @param string|false $message
     * @return VerifyCallable
     * @throws Throwable
     */
    public function throws($throws = null, $message = false): self
    {
        return $this->assertThrows($throws, $message);
    }

    /**
     * @param Exception|string|null $throws
     * @param string|false $message
     * @return $this
     */
    public function doesNotThrow($throws = null, $message = false): self
    {
        return $this->assertDoesNotThrow($throws, $message);
    }
}