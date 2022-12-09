<?php

declare(strict_types=1);

namespace Codeception\Verify\Expectations;

use Codeception\Verify\Expect;
use PHPUnit\Framework\Assert;

class ExpectBaseObject extends Expect
{
    use ExpectDataTrait;

    /**
     * ExpectBaseObject constructor
     *
     * @param object $object
     */
    public function __construct(object $object)
    {
        parent::__construct($object);
    }

    /**
     * Expect that an object does not have a specified attribute.
     *
     * @param string $attributeName
     * @param string $message
     * @return self
     */
    public function notToHaveAttribute(string $attributeName, string $message = ''): self
    {
        Assert::assertObjectNotHasAttribute($attributeName, $this->actual, $message);
        return $this;
    }

    /**
     * Expect that an object has a specified attribute.
     *
     * @param string $attributeName
     * @param string $message
     * @return self
     */
    public function toHaveAttribute(string $attributeName, string $message = ''): self
    {
        Assert::assertObjectHasAttribute($attributeName, $this->actual, $message);
        return $this;
    }
}