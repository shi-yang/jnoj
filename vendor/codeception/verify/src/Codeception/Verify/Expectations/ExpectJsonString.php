<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Expect;
use PHPUnit\Framework\Assert;

class ExpectJsonString extends Expect
{
    public function __construct(string $actualJson)
    {
        parent::__construct($actualJson);
    }

    /**
     * Expect that the generated JSON encoded object and the content of the given file are not equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notToEqualJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonStringNotEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return self
     */
    public function notToEqualJsonString(string $expectedJson, string $message = ''): self
    {
        Assert::assertJsonStringNotEqualsJsonString($expectedJson, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the generated JSON encoded object and the content of the given file are equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function toEqualJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonStringEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two given JSON encoded objects or arrays are equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return self
     */
    public function toEqualJsonString(string $expectedJson, string $message = ''): self
    {
        Assert::assertJsonStringEqualsJsonString($expectedJson, $this->actual, $message);
        return $this;
    }
}