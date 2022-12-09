<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Expect;
use PHPUnit\Framework\Assert;

class ExpectJsonFile extends Expect
{
    public function __construct(string $actualFile)
    {
        parent::__construct($actualFile);
    }

    /**
     * Expect that two JSON files are not equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notToEqualJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonFileNotEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two JSON files are equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function toEqualJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonFileEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }
}