<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;

class VerifyMixed extends Verify
{
    /**
     * VerifyMixed constructor.
     *
     * @param mixed $actual
     */
    public function __construct($actual)
    {
        parent::__construct($actual);
    }

    /**
     * Verifies that a variable is empty.
     *
     * @param string $message
     * @return self
     */
    public function empty(string $message = ''): self
    {
        Assert::assertEmpty($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are equal.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function equals($expected, string $message = ''): self
    {
        Assert::assertEquals($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are equal (canonicalizing).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function equalsCanonicalizing($expected, string $message = ''): self
    {
        Assert::assertEqualsCanonicalizing($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are equal (ignoring case).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function equalsIgnoringCase($expected, string $message = ''): self
    {
        Assert::assertEqualsIgnoringCase($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are equal (with delta).
     *
     * @param $expected
     * @param float $delta
     * @param string $message
     * @return self
     */
    public function equalsWithDelta($expected, float $delta, string $message = ''): self
    {
        Assert::assertEqualsWithDelta($expected, $this->actual, $delta, $message);
        return $this;
    }

    /**
     * Verifies that a condition is false.
     *
     * @param string $message
     * @return self
     */
    public function false(string $message = ''): self
    {
        Assert::assertFalse($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is finite.
     *
     * @param string $message
     * @return self
     */
    public function finite(string $message = ''): self
    {
        Assert::assertFinite($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a value is greater than another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function greaterThan($expected, string $message = ''): self
    {
        Assert::assertGreaterThan($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a value is greater than or equal to another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function greaterThanOrEqual($expected, string $message = ''): self
    {
        Assert::assertGreaterThanOrEqual($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is infinite.
     *
     * @param string $message
     * @return self
     */
    public function infinite(string $message = ''): self
    {
        Assert::assertInfinite($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return self
     */
    public function instanceOf(string $expected, string $message = ''): self
    {
        Assert::assertInstanceOf($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type array.
     *
     * @param string $message
     * @return self
     */
    public function isArray(string $message = ''): self
    {
        Assert::assertIsArray($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type bool.
     *
     * @param string $message
     * @return self
     */
    public function isBool(string $message = ''): self
    {
        Assert::assertIsBool($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type callable.
     *
     * @param string $message
     * @return self
     */
    public function isCallable(string $message = ''): self
    {
        Assert::assertIsCallable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type resource and is closed.
     *
     * @param string $message
     * @return self
     */
    public function isClosedResource(string $message = ''): self
    {
        Assert::assertIsClosedResource($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type float.
     *
     * @param string $message
     * @return self
     */
    public function isFloat(string $message = ''): self
    {
        Assert::assertIsFloat($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type int.
     *
     * @param string $message
     * @return self
     */
    public function isInt(string $message = ''): self
    {
        Assert::assertIsInt($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type iterable.
     *
     * @param string $message
     * @return self
     */
    public function isIterable(string $message = ''): self
    {
        Assert::assertIsIterable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type array.
     *
     * @param string $message
     * @return self
     */
    public function isNotArray(string $message = ''): self
    {
        Assert::assertIsNotArray($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type bool.
     *
     * @param string $message
     * @return self
     */
    public function isNotBool(string $message = ''): self
    {
        Assert::assertIsNotBool($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type callable.
     *
     * @param string $message
     * @return self
     */
    public function isNotCallable(string $message = ''): self
    {
        Assert::assertIsNotCallable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type resource.
     *
     * @param string $message
     * @return self
     */
    public function isNotClosedResource(string $message = ''): self
    {
        Assert::assertIsNotClosedResource($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type float.
     *
     * @param string $message
     * @return self
     */
    public function isNotFloat(string $message = ''): self
    {
        Assert::assertIsNotFloat($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type int.
     *
     * @param string $message
     * @return self
     */
    public function isNotInt(string $message = ''): self
    {
        Assert::assertIsNotInt($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type iterable.
     *
     * @param string $message
     * @return self
     */
    public function isNotIterable(string $message = ''): self
    {
        Assert::assertIsNotIterable($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type numeric.
     *
     * @param string $message
     * @return self
     */
    public function isNotNumeric(string $message = ''): self
    {
        Assert::assertIsNotNumeric($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type object.
     *
     * @param string $message
     * @return self
     */
    public function isNotObject(string $message = ''): self
    {
        Assert::assertIsNotObject($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type resource.
     *
     * @param string $message
     * @return self
     */
    public function isNotResource(string $message = ''): self
    {
        Assert::assertIsNotResource($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type scalar.
     *
     * @param string $message
     * @return self
     */
    public function isNotScalar(string $message = ''): self
    {
        Assert::assertIsNotScalar($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of type string.
     *
     * @param string $message
     * @return self
     */
    public function isNotString(string $message = ''): self
    {
        Assert::assertIsNotString($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type numeric.
     *
     * @param string $message
     * @return self
     */
    public function isNumeric(string $message = ''): self
    {
        Assert::assertIsNumeric($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type object.
     *
     * @param string $message
     * @return self
     */
    public function isObject(string $message = ''): self
    {
        Assert::assertIsObject($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type resource.
     *
     * @param string $message
     * @return self
     */
    public function isResource(string $message = ''): self
    {
        Assert::assertIsResource($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type scalar.
     *
     * @param string $message
     * @return self
     */
    public function isScalar(string $message = ''): self
    {
        Assert::assertIsScalar($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is of type string.
     *
     * @param string $message
     * @return self
     */
    public function isString(string $message = ''): self
    {
        Assert::assertIsString($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a value is smaller than another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function lessThan($expected, string $message = ''): self
    {
        Assert::assertLessThan($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a value is smaller than or equal to another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function lessThanOrEqual($expected, string $message = ''): self
    {
        Assert::assertLessThanOrEqual($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is nan.
     *
     * @param string $message
     * @return self
     */
    public function nan(string $message = ''): self
    {
        Assert::assertNan($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not empty.
     *
     * @param string $message
     * @return self
     */
    public function notEmpty(string $message = ''): self
    {
        Assert::assertNotEmpty($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are not equal.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notEquals($expected, string $message = ''): self
    {
        Assert::assertNotEquals($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are not equal (canonicalizing).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notEqualsCanonicalizing($expected, string $message = ''): self
    {
        Assert::assertNotEqualsCanonicalizing($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are not equal (ignoring case).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notEqualsIgnoringCase($expected, string $message = ''): self
    {
        Assert::assertNotEqualsIgnoringCase($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables are not equal (with delta).
     *
     * @param $expected
     * @param float $delta
     * @param string $message
     * @return self
     */
    public function notEqualsWithDelta($expected, float $delta, string $message = ''): self
    {
        Assert::assertNotEqualsWithDelta($expected, $this->actual, $delta, $message);
        return $this;
    }

    /**
     * Verifies that a condition is not false.
     *
     * @param string $message
     * @return self
     */
    public function notFalse(string $message = ''): self
    {
        Assert::assertNotFalse($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return self
     */
    public function notInstanceOf(string $expected, string $message = ''): self
    {
        Assert::assertNotInstanceOf($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is not null.
     *
     * @param string $message
     * @return self
     */
    public function notNull(string $message = ''): self
    {
        Assert::assertNotNull($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables do not have the same type and value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notSame($expected, string $message = ''): self
    {
        Assert::assertNotSame($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a condition is not true.
     *
     * @param string $message
     * @return self
     */
    public function notTrue(string $message = ''): self
    {
        Assert::assertNotTrue($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a variable is null.
     *
     * @param string $message
     * @return self
     */
    public function null(string $message = ''): self
    {
        Assert::assertNull($this->actual, $message);
        return $this;
    }

    /**
     * Verifies that two variables have the same type and value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function same($expected, string $message = ''): self
    {
        Assert::assertSame($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a condition is true.
     *
     * @param string $message
     * @return self
     */
    public function true(string $message = ''): self
    {
        Assert::assertTrue($this->actual, $message);
        return $this;
    }
}