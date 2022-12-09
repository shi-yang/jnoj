<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Expect;
use PHPUnit\Framework\Assert;

class ExpectString extends Expect
{
    public function __construct(string $string)
    {
        parent::__construct($string);
    }

    public function notToContainString(string $needle, string $message = ''): self
    {
        Assert::assertStringNotContainsString($needle, $this->actual, $message);
        return $this;
    }

    public function notToContainStringIgnoringCase(string $needle, string $message = ''): self
    {
        Assert::assertStringNotContainsStringIgnoringCase($needle, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string ends not with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return self
     */
    public function notToEndWith(string $suffix, string $message = ''): self
    {
        Assert::assertStringEndsNotWith($suffix, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of a string is not equal to the contents of a file.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notToEqualFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of a string is not equal to the contents of a file (canonicalizing).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notToEqualFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFileCanonicalizing($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of a string is not equal to the contents of a file (ignoring case).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notToEqualFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFileIgnoringCase($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string does not match a given format string.
     *
     * @param $format
     * @param string $message
     * @return self
     */
    public function notToMatchFormat(string $format, string $message = ''): self
    {
        Assert::assertStringNotMatchesFormat($format, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string does not match a given format string.
     *
     * @param string $formatFile
     * @param string $message
     * @return self
     */
    public function notToMatchFormatFile(string $formatFile, string $message = ''): self
    {
        Assert::assertStringNotMatchesFormatFile($formatFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string does not match a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return self
     */
    public function notToMatchRegExp(string $pattern, string $message = ''): self
    {
        Assert::assertDoesNotMatchRegularExpression($pattern, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string starts not with a given prefix.
     *
     * @param string $prefix
     * @param string $message
     * @return self
     */
    public function notToStartWith(string $prefix, string $message = ''): self
    {
        Assert::assertStringStartsNotWith($prefix, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string is a valid JSON string.
     *
     * @param string $message
     * @return self
     */
    public function toBeJson(string $message = ''): self
    {
        Assert::assertJson($this->actual, $message);
        return $this;
    }

    public function toContainString(string $needle, string $message = ''): self
    {
        Assert::assertStringContainsString($needle, $this->actual, $message);
        return $this;
    }

    public function toContainStringIgnoringCase(string $needle, string $message = ''): self
    {
        Assert::assertStringContainsStringIgnoringCase($needle, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string ends with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return self
     */
    public function toEndWith(string $suffix, string $message = ''): self
    {
        Assert::assertStringEndsWith($suffix, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of a string is equal to the contents of a file.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function toEqualFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of a string is equal to the contents of a file (canonicalizing).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function toEqualFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFileCanonicalizing($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that the contents of a string is equal to the contents of a file (ignoring case).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function toEqualFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFileIgnoringCase($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string matches a given format string.
     *
     * @param string $format
     * @param string $message
     * @return self
     */
    public function toMatchFormat(string $format, string $message = ''): self
    {
        Assert::assertStringMatchesFormat($format, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string matches a given format file.
     *
     * @param string $formatFile
     * @param string $message
     * @return self
     */
    public function toMatchFormatFile(string $formatFile, string $message = ''): self
    {
        Assert::assertStringMatchesFormatFile($formatFile, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string matches a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return self
     */
    public function toMatchRegExp(string $pattern, string $message = ''): self
    {
        Assert::assertMatchesRegularExpression($pattern, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a string starts with a given prefix.
     *
     * @param string $prefix
     * @param string $message
     * @return self
     */
    public function toStartWith(string $prefix, string $message = ''): self
    {
        Assert::assertStringStartsWith($prefix, $this->actual, $message);
        return $this;
    }
}