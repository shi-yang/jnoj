<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Exception\InvalidVerifyException;
use Codeception\Verify\Expect;
use DOMDocument;
use PHPUnit\Framework\Assert;
use function basename;
use function is_string;

class ExpectXmlString extends Expect
{
    /**
     * ExpectXmlString constructor
     *
     * @param DOMDocument|string $actualXml
     */
    public function __construct($actualXml)
    {
        if (is_string($actualXml) || $actualXml instanceof DOMDocument) {
            parent::__construct($actualXml);
            return;
        }
        
        throw new InvalidVerifyException(basename(self::class), $actualXml);
    }

    /**
     * Expect that two XML documents are not equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notToEqualXmlFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertXmlStringNotEqualsXmlFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two XML documents are not equal.
     *
     * @param DOMDocument|string $expectedXml
     * @param string $message
     * @return self
     */
    public function notToEqualXmlString($expectedXml, string $message = ''): self
    {
        Assert::assertXmlStringNotEqualsXmlString($expectedXml, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two XML documents are equal.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function toEqualXmlFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertXmlStringEqualsXmlFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two XML documents are equal.
     *
     * @param DOMDocument|string $expectedXml
     * @param string $message
     * @return self
     */
    public function toEqualXmlString($expectedXml, string $message = ''): self
    {
        Assert::assertXmlStringEqualsXmlString($expectedXml, $this->actual, $message);
        return $this;
    }
}