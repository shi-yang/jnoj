<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Expect;
use PHPUnit\Framework\Assert;

class ExpectFile extends Expect
{
    use ExpectDataTrait;

    public function __construct(string $actual)
    {
        parent::__construct($actual);
    }

    /**
     * Expect that a file does not exist.
     *
     * @param string $message
     * @return self
     */
    public function notToExist(string $message = ''): self
    {
        Assert::assertFileDoesNotExist($this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of one file is equal to the contents of another file.
     *
     * @param string $expected
     * @param string $message
     * @return self
     */
    public function toBeEqual(string $expected, string $message = ''): self
    {
        Assert::assertFileEquals($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of one file is equal to the contents of another file (canonicalizing).
     *
     * @param string $expected
     * @param string $message
     * @return self
     */
    public function toBeEqualCanonicalizing(string $expected, string $message = ''): self
    {
        Assert::assertFileEqualsCanonicalizing($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of one file is equal to the contents of another file (ignoring case).
     *
     * @param string $expected
     * @param string $message
     * @return self
     */
    public function toBeEqualIgnoringCase(string $expected, string $message = ''): self
    {
        Assert::assertFileEqualsIgnoringCase($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file exists.
     *
     * @param string $message
     * @return self
     */
    public function toExist(string $message = ''): self
    {
        Assert::assertFileExists($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file exists and is not readable.
     *
     * @param string $message
     * @return self
     */
    public function toExistAndNotToBeReadable(string $message = ''): self
    {
        Assert::assertFileIsNotReadable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file exists and is not writable.
     *
     * @param string $message
     * @return self
     */
    public function toExistAndNotToBeWritable(string $message = ''): self
    {
        Assert::assertFileIsNotWritable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file exists and is readable.
     *
     * @param string $message
     * @return self
     */
    public function toExistAndToBeReadable(string $message = ''): self
    {
        Assert::assertFileIsReadable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a file exists and is writable.
     *
     * @param string $message
     * @return self
     */
    public function toExistAndToBeWritable(string $message = ''): self
    {
        Assert::assertFileIsWritable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of one file is not equal to the contents of another file.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toNotEqual(string $expected, string $message = ''): self
    {
        Assert::assertFileNotEquals($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of one file is not equal to the contents of another file (canonicalizing).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toNotEqualCanonicalizing(string $expected, string $message = ''): self
    {
        Assert::assertFileNotEqualsCanonicalizing($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of one file is not equal to the contents of another file (ignoring case).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toNotEqualIgnoringCase(string $expected, string $message = ''): self
    {
        Assert::assertFileNotEqualsIgnoringCase($expected, $this->actual, $message);
        return $this;
    }
}