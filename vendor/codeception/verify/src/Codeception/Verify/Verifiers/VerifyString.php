<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;

class VerifyString extends Verify
{
    public function __construct(string $string)
    {
        parent::__construct($string);
    }

    public function containsString(string $needle, string $message = ''): self
    {
        Assert::assertStringContainsString($needle, $this->actual, $message);
        return $this;
    }

    public function containsStringIgnoringCase(string $needle, string $message = ''): self
    {
        Assert::assertStringContainsStringIgnoringCase($needle, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string does not match a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return self
     */
    public function doesNotMatchRegExp(string $pattern, string $message = ''): self
    {
        Assert::assertDoesNotMatchRegularExpression($pattern, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string ends with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return self
     */
    public function endsWith(string $suffix, string $message = ''): self
    {
        Assert::assertStringEndsWith($suffix, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the contents of a string is equal to the contents of a file.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function equalsFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the contents of a string is equal to the contents of a file (canonicalizing).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function equalsFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFileCanonicalizing($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the contents of a string is equal to the contents of a file (ignoring case).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function equalsFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFileIgnoringCase($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string is a valid JSON string.
     *
     * @param string $message
     * @return self
     */
    public function json(string $message = ''): self
    {
        Assert::assertJson($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string matches a given format string.
     *
     * @param string $format
     * @param string $message
     * @return self
     */
    public function matchesFormat(string $format, string $message = ''): self
    {
        Assert::assertStringMatchesFormat($format, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string matches a given format file.
     *
     * @param string $formatFile
     * @param string $message
     * @return self
     */
    public function matchesFormatFile(string $formatFile, string $message = ''): self
    {
        Assert::assertStringMatchesFormatFile($formatFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string matches a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return self
     */
    public function matchesRegExp(string $pattern, string $message = ''): self
    {
        Assert::assertMatchesRegularExpression($pattern, $this->actual, $message);
        return $this;
    }

    public function notContainsString(string $needle, string $message = ''): self
    {
        Assert::assertStringNotContainsString($needle, $this->actual, $message);
        return $this;
    }

    public function notContainsStringIgnoringCase(string $needle, string $message = ''): self
    {
        Assert::assertStringNotContainsStringIgnoringCase($needle, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string ends not with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return self
     */
    public function notEndsWith(string $suffix, string $message = ''): self
    {
        Assert::assertStringEndsNotWith($suffix, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the contents of a string is not equal to the contents of a file.
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notEqualsFile(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFile($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the contents of a string is not equal to the contents of a file (canonicalizing).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notEqualsFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFileCanonicalizing($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the contents of a string is not equal to the contents of a file (ignoring case).
     *
     * @param string $expectedFile
     * @param string $message
     * @return self
     */
    public function notEqualsFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFileIgnoringCase($expectedFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string does not match a given format string.
     *
     * @param $format
     * @param string $message
     * @return self
     */
    public function notMatchesFormat(string $format, string $message = ''): self
    {
        Assert::assertStringNotMatchesFormat($format, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string does not match a given format string.
     *
     * @param string $formatFile
     * @param string $message
     * @return self
     */
    public function notMatchesFormatFile(string $formatFile, string $message = ''): self
    {
        Assert::assertStringNotMatchesFormatFile($formatFile, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string starts not with a given prefix.
     *
     * @param string $prefix
     * @param string $message
     * @return self
     */
    public function startsNotWith(string $prefix, string $message = ''): self
    {
        Assert::assertStringStartsNotWith($prefix, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a string starts with a given prefix.
     *
     * @param string $prefix
     * @param string $message
     * @return self
     */
    public function startsWith(string $prefix, string $message = ''): self
    {
        Assert::assertStringStartsWith($prefix, $this->actual, $message);
        return $this;
    }
}