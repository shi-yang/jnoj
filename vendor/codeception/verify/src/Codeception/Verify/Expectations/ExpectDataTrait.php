<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use PHPUnit\Framework\Assert;

trait ExpectDataTrait
{
    /**
     * Expect that a file/dir is not readable.
     *
     * @param string $message
     * @return self
     */
    public function notToBeReadable(string $message = ''): self
    {
        Assert::assertIsNotReadable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file/dir is not writable.
     *
     * @param string $message
     * @return self
     */
    public function notToBeWritable(string $message = ''): self
    {
        Assert::assertIsNotWritable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file/dir is readable.
     *
     * @param string $message
     * @return self
     */
    public function toBeReadable(string $message = ''): self
    {
        Assert::assertIsReadable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file/dir is writable.
     *
     * @param string $message
     * @return self
     */
    public function toBeWritable(string $message = ''): self
    {
        Assert::assertIsWritable($this->actual, $message);
        return $this;
    }
}