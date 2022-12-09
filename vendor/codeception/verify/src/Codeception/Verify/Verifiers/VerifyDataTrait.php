<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use PHPUnit\Framework\Assert;

trait VerifyDataTrait
{
    /**
     * Verifies that a file/dir is not readable.
     *
     * @param string $message
     * @return self
     */
    public function isNotReadable(string $message = ''): self
    {
        Assert::assertIsNotReadable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a file/dir is not writable.
     *
     * @param string $message
     * @return self
     */
    public function isNotWritable(string $message = ''): self
    {
        Assert::assertIsNotWritable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a file/dir is readable.
     *
     * @param string $message
     * @return self
     */
    public function isReadable(string $message = ''): self
    {
        Assert::assertIsReadable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a file/dir is writable.
     *
     * @param string $message
     * @return self
     */
    public function isWritable(string $message = ''): self
    {
        Assert::assertIsWritable($this->actual, $message);
        return $this;
    }
}