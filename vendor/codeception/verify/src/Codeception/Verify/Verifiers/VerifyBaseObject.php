<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;

class VerifyBaseObject extends Verify
{
    use VerifyDataTrait;

    /**
     * VerifyBaseObject constructor
     *
     * @param object $object
     */
    public function __construct(object $object)
    {
        parent::__construct($object);
    }

    /**
     * Verifies that an object has a specified attribute.
     *
     * @param string $attributeName
     * @param string $message
     * @return self
     */
    public function hasAttribute(string $attributeName, string $message = ''): self
    {
        Assert::assertObjectHasAttribute($attributeName, $this->actual, $message);
        return $this;
    }

    /**
     * Verifies that an object does not have a specified attribute.
     *
     * @param string $attributeName
     * @param string $message
     * @return self
     */
    public function notHasAttribute(string $attributeName, string $message = ''): self
    {
        Assert::assertObjectNotHasAttribute($attributeName, $this->actual, $message);
        return $this;
    }
}