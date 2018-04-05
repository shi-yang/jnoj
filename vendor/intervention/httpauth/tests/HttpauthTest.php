<?php

use Intervention\Httpauth\Httpauth;

class HttpauthTest extends PHPUnit_Framework_TestCase
{
    private function createTestHttpauth()
    {
        $config = array(
            'realm' => 'test_realm',
            'username' => 'test_user',
            'password' => 'test_password'
        );

        $httpauth = new Httpauth($config);
        return $httpauth;
    }

    public function testConstruction()
    {
        $httpauth = $this->createTestHttpauth();
        $this->assertInstanceOf('\Intervention\Httpauth\Httpauth', $httpauth);
        $this->assertEquals('test_realm', $httpauth->realm);
        $this->assertTrue($httpauth->isValid('test_user', 'test_password'));
    }

    public function testStaticCall()
    {
        $config = array(
            'realm' => '1',
            'username' => '2',
            'password' => '3'
        );

        $httpauth = Httpauth::make($config);

        $this->assertInstanceOf('\Intervention\Httpauth\Httpauth', $httpauth);
        $this->assertEquals('1', $httpauth->realm);
        $this->assertTrue($httpauth->isValid($config['username'], $config['password']));
    }

    /**
     * @expectedException Exception
     */
    public function testConstructorWithoutUserPassword()
    {
        $httpauth = new Httpauth;
    }
}
