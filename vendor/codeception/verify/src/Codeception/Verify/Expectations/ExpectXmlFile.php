<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Expect;
use PHPUnit\Framework\Assert;

class ExpectXmlFile extends Expect
{
    public function __construct(string $actualFile)
    {
        parent::__construct($actualFile);
    }

    /**
     * Expect that two XML files are not equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notToEqualXmlFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertXmlFileNotEqualsXmlFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two XML files are equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function toEqualXmlFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertXmlFileEqualsXmlFile($expectedFile, $this->actual, $message);
        return $this;
    }
}