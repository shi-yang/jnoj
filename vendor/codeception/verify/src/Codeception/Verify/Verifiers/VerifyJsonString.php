<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;

class VerifyJsonString extends Verify
{
    public function __construct(string $actualJson)
    {
        parent::__construct($actualJson);
    }

    /**
     * Verifies that the generated JSON encoded object and the content of the given file are equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function equalsJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonStringEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two given JSON encoded objects or arrays are equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return self
     */
    public function equalsJsonString(string $expectedJson, string $message = ''): self
    {
        Assert::assertJsonStringEqualsJsonString($expectedJson, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the generated JSON encoded object and the content of the given file are not equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notEqualsJsonFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertJsonStringNotEqualsJsonFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return self
     */
    public function notEqualsJsonString(string $expectedJson, string $message = ''): self
    {
        Assert::assertJsonStringNotEqualsJsonString($expectedJson, $this->actual, $message);
        return $this;
    }
}