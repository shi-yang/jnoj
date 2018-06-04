<?php

use Intervention\Httpauth\BasicUser;

class BasicUserTest extends PHPUnit_Framework_TestCase
{
    public function testBasicUserAuthMod()
    {
        $_SERVER['PHP_AUTH_USER'] = 'test_user';
        $_SERVER['PHP_AUTH_PW'] = 'test_password';

        $user = new BasicUser;
        $this->assertTrue($user->isValid('test_user', 'test_password'));
    }

    public function testUserAuth()
    {
        $userdata = array('test_user', 'test_password');
        $userdata = implode(':', $userdata);
        $userdata = base64_encode($userdata);
        $userdata = 'basic_'.$userdata;

        unset($_SERVER['PHP_AUTH_USER']);
        unset($_SERVER['PHP_AUTH_PW']);
        $_SERVER['HTTP_AUTHENTICATION'] = $userdata;

        $user = new BasicUser;
        $this->assertTrue($user->isValid('test_user', 'test_password'));
    }
}
