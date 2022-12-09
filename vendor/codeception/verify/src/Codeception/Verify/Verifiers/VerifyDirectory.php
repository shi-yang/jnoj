<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;

class VerifyDirectory extends Verify
{
    use VerifyDataTrait;

    /**
     * VerifyDirectory constructor
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        parent::__construct($directory);
    }

    /**
     * Verifies that a directory does not exist.
     *
     * @param string $message
     * @return self
     */
    public function doesNotExist(string $message = ''): self
    {
        Assert::assertDirectoryDoesNotExist($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a directory exists.
     *
     * @param string $message
     * @return self
     */
    public function exists(string $message = ''): self
    {
        Assert::assertDirectoryExists($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a directory exists and is not readable.
     *
     * @param string $message
     * @return self
     */
    public function existsAndIsNotReadable(string $message = ''): self
    {
        Assert::assertDirectoryIsNotReadable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a directory exists and is not writable.
     *
     * @param string $message
     * @return self
     */
    public function existsAndIsNotWritable(string $message = ''): self
    {
        Assert::assertDirectoryIsNotWritable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a directory exists and is readable.
     *
     * @param string $message
     * @return self
     */
    public function existsAndIsReadable(string $message = ''): self
    {
        Assert::assertDirectoryIsReadable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a directory exists and is writable.
     *
     * @param string $message
     * @return self
     */
    public function existsAndIsWritable(string $message = ''): self
    {
        Assert::assertDirectoryIsWritable($this->actual, $message);
        return $this;
    }
}