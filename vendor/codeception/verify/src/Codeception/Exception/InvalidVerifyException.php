<?php

declare(strict_types=1);

namespace Codeception\Exception;

use InvalidArgumentException;
use function gettype;
use function sprintf;

final class InvalidVerifyException extends InvalidArgumentException
{
    public function __construct($verifyName, $actual)
    {
        $message = sprintf(
            "%s type cannot be used with %s verify.",
            gettype($actual),
            $verifyName
        );

        parent::__construct($message);
    }
}