<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use ArrayAccess;
use Codeception\Exception\InvalidVerifyException;
use Codeception\Verify\Verify;
use Countable;
use PHPUnit\Framework\Assert;
use function basename;
use function is_array;
use function is_iterable;

class VerifyArray extends Verify
{
    /**
     * VerifyArray constructor
     *
     * @param array|ArrayAccess|Countable|iterable $actual
     */
    public function __construct($actual)
    {
        if (
            is_array($actual) ||
            $actual instanceof ArrayAccess ||
            $actual instanceof Countable ||
            is_iterable($actual)
        ) {
            parent::__construct($actual);
            return;
        }
        
        throw new InvalidVerifyException(basename(self::class), $actual);
    }

    /**
     * Verifies that a haystack contains a needle.
     *
     * @param $needle
     * @param string $message
     * @return self
     */
    public function contains($needle, string $message = ''): self
    {
        Assert::assertContains($needle, $this->actual, $message);
        return $this;
    }

    public function containsEquals($needle, string $message = ''): self
    {
        Assert::assertContainsEquals($needle, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a haystack contains only values of a given type.
     *
     * @param string $type
     * @param bool|null $isNativeType
     * @param string $message
     * @return self
     */
    public function containsOnly(string $type, ?bool $isNativeType = null, string $message = ''): self
    {
        Assert::assertContainsOnly($type, $this->actual, $isNativeType, $message);
        return $this;
    }

    /**
     * Verifies that a haystack contains only instances of a given class name.
     *
     * @param string $className
     * @param string $message
     * @return self
     */
    public function containsOnlyInstancesOf(string $className, string $message = ''): self
    {
        Assert::assertContainsOnlyInstancesOf($className, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies the number of elements of an array, Countable or Traversable.
     *
     * @param int $expectedCount
     * @param string $message
     * @return self
     */
    public function count(int $expectedCount, string $message = ''): self
    {
        Assert::assertCount($expectedCount, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that an array has a specified key.
     *
     * @param int|string $key
     * @param string $message
     * @return self
     */
    public function hasKey($key, string $message = ''): self
    {
        Assert::assertArrayHasKey($key, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that an array does not have a specified key.
     *
     * @param int|string $key
     * @param string $message
     * @return self
     */
    public function hasNotKey($key, string $message = ''): self
    {
        Assert::assertArrayNotHasKey($key, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a haystack does not contain a needle.
     *
     * @param $needle
     * @param string $message
     * @return self
     */
    public function notContains($needle, string $message = ''): self
    {
        Assert::assertNotContains($needle, $this->actual, $message);
        return $this;
    }

    public function notContainsEquals($needle, string $message = ''): self
    {
        Assert::assertNotContainsEquals($needle, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that a haystack does not contain only values of a given type.
     *
     * @param string $type
     * @param bool|null $isNativeType
     * @param string $message
     * @return self
     */
    public function notContainsOnly(string $type, ?bool $isNativeType = null, string $message = ''): self
    {
        Assert::assertNotContainsOnly($type, $this->actual, $isNativeType, $message);
        return $this;
    }

    /**
     * Verifies the number of elements of an array, Countable or Traversable.
     *
     * @param int $expectedCount
     * @param string $message
     * @return self
     */
    public function notCount(int $expectedCount, string $message = ''): self
    {
        Assert::assertNotCount($expectedCount, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the size of two arrays (or `Countable` or `Traversable` objects) is not the same.
     *
     * @param Countable|iterable $expected
     * @param string $message
     * @return self
     */
    public function notSameSize($expected, string $message = ''): self
    {
        Assert::assertNotSameSize($expected, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that the size of two arrays (or `Countable` or `Traversable` objects) is the same.
     *
     * @param Countable|iterable $expected
     * @param string $message
     * @return self
     */
    public function sameSize($expected, string $message = ''): self
    {
        Assert::assertSameSize($expected, $this->actual, $message);
        return $this;
    }
}