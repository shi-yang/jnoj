<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Expect;
use PHPUnit\Framework\Assert;

class ExpectMixed extends Expect
{
    /**
     * ExpectMixed constructor.
     *
     * @param mixed $actual
     */
    public function __construct($actual)
    {
        parent::__construct($actual);
    }

    /**
     * Expect that two variables do not have the same type and value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notToBe($expected, string $message = ''): self
    {
        Assert::assertNotSame($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type array.
     *
     * @param string $message
     * @return self
     */
    public function notToBeArray(string $message = ''): self
    {
        Assert::assertIsNotArray($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type bool.
     *
     * @param string $message
     * @return self
     */
    public function notToBeBool(string $message = ''): self
    {
        Assert::assertIsNotBool($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type callable.
     *
     * @param string $message
     * @return self
     */
    public function notToBeCallable(string $message = ''): self
    {
        Assert::assertIsNotCallable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type resource.
     *
     * @param string $message
     * @return self
     */
    public function notToBeClosedResource(string $message = ''): self
    {
        Assert::assertIsNotClosedResource($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not empty.
     *
     * @param string $message
     * @return self
     */
    public function notToBeEmpty(string $message = ''): self
    {
        Assert::assertNotEmpty($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a condition is not false.
     *
     * @param string $message
     * @return self
     */
    public function notToBeFalse(string $message = ''): self
    {
        Assert::assertNotFalse($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type float.
     *
     * @param string $message
     * @return self
     */
    public function notToBeFloat(string $message = ''): self
    {
        Assert::assertIsNotFloat($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return self
     */
    public function notToBeInstanceOf(string $expected, string $message = ''): self
    {
        Assert::assertNotInstanceOf($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type int.
     *
     * @param string $message
     * @return self
     */
    public function notToBeInt(string $message = ''): self
    {
        Assert::assertIsNotInt($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type iterable.
     *
     * @param string $message
     * @return self
     */
    public function notToBeIterable(string $message = ''): self
    {
        Assert::assertIsNotIterable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not null.
     *
     * @param string $message
     * @return self
     */
    public function notToBeNull(string $message = ''): self
    {
        Assert::assertNotNull($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type numeric.
     *
     * @param string $message
     * @return self
     */
    public function notToBeNumeric(string $message = ''): self
    {
        Assert::assertIsNotNumeric($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type object.
     *
     * @param string $message
     * @return self
     */
    public function notToBeObject(string $message = ''): self
    {
        Assert::assertIsNotObject($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type resource.
     *
     * @param string $message
     * @return self
     */
    public function notToBeResource(string $message = ''): self
    {
        Assert::assertIsNotResource($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type scalar.
     *
     * @param string $message
     * @return self
     */
    public function notToBeScalar(string $message = ''): self
    {
        Assert::assertIsNotScalar($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is not of type string.
     *
     * @param string $message
     * @return self
     */
    public function notToBeString(string $message = ''): self
    {
        Assert::assertIsNotString($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a condition is not true.
     *
     * @param string $message
     * @return self
     */
    public function notToBeTrue(string $message = ''): self
    {
        Assert::assertNotTrue($this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are not equal.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notToEqual($expected, string $message = ''): self
    {
        Assert::assertNotEquals($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are not equal (canonicalizing).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notToEqualCanonicalizing($expected, string $message = ''): self
    {
        Assert::assertNotEqualsCanonicalizing($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are not equal (ignoring case).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function notToEqualIgnoringCase($expected, string $message = ''): self
    {
        Assert::assertNotEqualsIgnoringCase($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are not equal (with delta).
     *
     * @param $expected
     * @param float $delta
     * @param string $message
     * @return self
     */
    public function notToEqualWithDelta($expected, float $delta, string $message = ''): self
    {
        Assert::assertNotEqualsWithDelta($expected, $this->actual, $delta, $message);
        return $this;
    }

    /**
     * Expect that two variables have the same type and value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toBe($expected, string $message = ''): self
    {
        Assert::assertSame($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type array.
     *
     * @param string $message
     * @return self
     */
    public function toBeArray(string $message = ''): self
    {
        Assert::assertIsArray($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type bool.
     *
     * @param string $message
     * @return self
     */
    public function toBeBool(string $message = ''): self
    {
        Assert::assertIsBool($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type callable.
     *
     * @param string $message
     * @return self
     */
    public function toBeCallable(string $message = ''): self
    {
        Assert::assertIsCallable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type resource and is closed.
     *
     * @param string $message
     * @return self
     */
    public function toBeClosedResource(string $message = ''): self
    {
        Assert::assertIsClosedResource($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is empty.
     *
     * @param string $message
     * @return self
     */
    public function toBeEmpty(string $message = ''): self
    {
        Assert::assertEmpty($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a condition is false.
     *
     * @param string $message
     * @return self
     */
    public function toBeFalse(string $message = ''): self
    {
        Assert::assertFalse($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is finite.
     *
     * @param string $message
     * @return self
     */
    public function toBeFinite(string $message = ''): self
    {
        Assert::assertFinite($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type float.
     *
     * @param string $message
     * @return self
     */
    public function toBeFloat(string $message = ''): self
    {
        Assert::assertIsFloat($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a value is greater than another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toBeGreaterThan($expected, string $message = ''): self
    {
        Assert::assertGreaterThan($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a value is greater than or equal to another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toBeGreaterThanOrEqualTo($expected, string $message = ''): self
    {
        Assert::assertGreaterThanOrEqual($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is infinite.
     *
     * @param string $message
     * @return self
     */
    public function toBeInfinite(string $message = ''): self
    {
        Assert::assertInfinite($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return self
     */
    public function toBeInstanceOf(string $expected, string $message = ''): self
    {
        Assert::assertInstanceOf($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type int.
     *
     * @param string $message
     * @return self
     */
    public function toBeInt(string $message = ''): self
    {
        Assert::assertIsInt($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type iterable.
     *
     * @param string $message
     * @return self
     */
    public function toBeIterable(string $message = ''): self
    {
        Assert::assertIsIterable($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a value is smaller than another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toBeLessThan($expected, string $message = ''): self
    {
        Assert::assertLessThan($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a value is smaller than or equal to another value.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toBeLessThanOrEqualTo($expected, string $message = ''): self
    {
        Assert::assertLessThanOrEqual($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is nan.
     *
     * @param string $message
     * @return self
     */
    public function toBeNan(string $message = ''): self
    {
        Assert::assertNan($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is null.
     *
     * @param string $message
     * @return self
     */
    public function toBeNull(string $message = ''): self
    {
        Assert::assertNull($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type numeric.
     *
     * @param string $message
     * @return self
     */
    public function toBeNumeric(string $message = ''): self
    {
        Assert::assertIsNumeric($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type object.
     *
     * @param string $message
     * @return self
     */
    public function toBeObject(string $message = ''): self
    {
        Assert::assertIsObject($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type resource.
     *
     * @param string $message
     * @return self
     */
    public function toBeResource(string $message = ''): self
    {
        Assert::assertIsResource($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type scalar.
     *
     * @param string $message
     * @return self
     */
    public function toBeScalar(string $message = ''): self
    {
        Assert::assertIsScalar($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a variable is of type string.
     *
     * @param string $message
     * @return self
     */
    public function toBeString(string $message = ''): self
    {
        Assert::assertIsString($this->actual, $message);
        return $this;
    }

    /**
     * Expect that a condition is true.
     *
     * @param string $message
     * @return self
     */
    public function toBeTrue(string $message = ''): self
    {
        Assert::assertTrue($this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are equal.
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toEqual($expected, string $message = ''): self
    {
        Assert::assertEquals($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are equal (canonicalizing).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toEqualCanonicalizing($expected, string $message = ''): self
    {
        Assert::assertEqualsCanonicalizing($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are equal (ignoring case).
     *
     * @param $expected
     * @param string $message
     * @return self
     */
    public function toEqualIgnoringCase($expected, string $message = ''): self
    {
        Assert::assertEqualsIgnoringCase($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that two variables are equal (with delta).
     *
     * @param $expected
     * @param float $delta
     * @param string $message
     * @return self
     */
    public function toEqualWithDelta($expected, float $delta, string $message = ''): self
    {
        Assert::assertEqualsWithDelta($expected, $this->actual, $delta, $message);
        return $this;
    }
}