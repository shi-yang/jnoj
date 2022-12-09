<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;

class VerifyJsonFile extends Verify
{
    public function __construct(string $actualFile)
    {
        parent::__construct($actualFile);
    }

    /**
     * Verifies that two JSON files are equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function equalsJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonFileEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two JSON files are not equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notEqualsJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonFileNotEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }
}