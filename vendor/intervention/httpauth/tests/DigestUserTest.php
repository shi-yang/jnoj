<?php

use Intervention\Httpauth\DigestUser;

class DigestUserTest extends PHPUnit_Framework_TestCase
{
    public function testDigestUserCreation()
    {
        $user = new DigestUser;
        $this->assertInstanceOf('\Intervention\Httpauth\DigestUser', $user);
    }
}
