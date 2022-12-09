<?php

declare(strict_types=1);

use Codeception\Verify\Expectations\ExpectAny;
use Codeception\Verify\Verifiers\VerifyAny;

if (!function_exists('verify'))
{
    /**
     * @param mixed $actual
     * @return VerifyAny
     */
    function verify($actual): VerifyAny
    {
        return new VerifyAny($actual);
    }
}

if (!function_exists('verify_that'))
{
    /**
     * @param mixed $actual
     * @return VerifyAny
     */
    function verify_that($actual): VerifyAny
    {
        return new VerifyAny($actual);
    }
}

if (!function_exists('expect'))
{
    /**
     * @param mixed $actual
     * @return ExpectAny
     */
    function expect($actual): ExpectAny {
        return new ExpectAny($actual);
    }
}