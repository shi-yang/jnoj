<?php

namespace Props;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CLASS = 'Props\ContainerTestObject';

    public function testBasicInterop()
    {
        $di = new Container();
        $this->assertInstanceOf('Interop\Container\ContainerInterface', $di);

        $this->assertFalse($di->has('foo'));
        $di->foo = 'bar';
        $this->assertTrue($di->has('foo'));
    }

    /**
     * @expectedException \Interop\Container\Exception\NotFoundException
     */
    public function testInteropNotFound()
    {
        $di = new Container();
        $di->get('foo');
    }

    /**
     * @expectedException \Interop\Container\Exception\ContainerException
     */
    public function testInteropException1()
    {
        $di = new Container();
        $di->setFactory('foo', null);
    }

    /**
     * @expectedException \Interop\Container\Exception\ContainerException
     */
    public function testInteropException2()
    {
        $di = new Container();
        $di->setFactory('foo', function () {
            throw new \Exception();
        });
        $di->foo;
    }

    public function testEmpty()
    {
        $di = new Container();
        $this->assertFalse(isset($di->foo));
        $this->assertFalse($di->has('foo'));
    }

    public function testValueSetRemovesFactory()
    {
        $di = new Container();
        $di->foo = function () {
            return 'Bar';
        };
        $di->foo = 'Foo';
        $this->assertTrue(isset($di->foo));
        $this->assertFalse($di->hasFactory('foo'));
    }

    public function testSetResolvable()
    {
        $di = new Container();
        $di->foo = function () {
            return new ContainerTestObject();
        };

        $this->assertTrue(isset($di->foo));
        $this->assertTrue($di->has('foo'));
        $this->assertTrue($di->hasFactory('foo'));
    }

    /**
     * @expectedException \Props\NotFoundException
     */
    public function testReadMissingValue()
    {
        $di = new Container();
        $di->foo;
    }

    /**
     * @expectedException \Props\NotFoundException
     */
    public function testGetMissingValue()
    {
        $di = new Container();
        $di->get('foo');
    }

    public function testGetNewUnresolvableValue()
    {
        $di = new Container();
        $di->foo = 'Foo';

        $this->setExpectedException('Props\NotFoundException');
        $di->new_foo();
    }

    public function testSetAfterRead()
    {
        $di = new Container();

        $di->foo = 'Foo';
        $di->foo = 'Foo2';
        $this->assertEquals('Foo2', $di->foo);
    }

    public function testHandlesNullValue()
    {
        $di = new Container();
        $di->null = null;
        $this->assertTrue(isset($di->null));
        $this->assertTrue($di->has('null'));
        $this->assertNull($di->null);
        $this->assertNull($di->get('null'));
    }

    public function testFactoryReceivesContainer()
    {
        $di = new Container();
        $di->foo = function () {
            return func_get_args();
        };
        $foo = $di->foo;
        $this->assertSame($foo[0], $di);
        $this->assertEquals(count($foo), 1);
    }

    public function testGetResolvables()
    {
        $di = new Container();

        $di->foo = function () {
            return new ContainerTestObject();
        };
        $foo1 = $di->foo;
        $foo2 = $di->foo;
        $this->assertInstanceOf(self::TEST_CLASS, $foo1);
        $this->assertSame($foo1, $foo2);

        $foo3 = $di->new_foo();
        $foo4 = $di->new_foo();
        $this->assertInstanceOf(self::TEST_CLASS, $foo3);
        $this->assertInstanceOf(self::TEST_CLASS, $foo4);
        $this->assertNotSame($foo3, $foo4);
        $this->assertNotSame($foo1, $foo3);
    }

    public function testKeyNamespace()
    {
        $di = new Container();
        $di->foo = function () {
            return new ContainerTestObject();
        };
        $di->new_foo = 'Foo';

        $this->assertInstanceOf(self::TEST_CLASS, $di->new_foo());
        $this->assertEquals('Foo', $di->new_foo);
    }

    public function testUnset()
    {
        $di = new Container();
        $di->foo = 'Foo';

        unset($di->foo);
        $this->assertFalse(isset($di->foo));
    }

    public function testAccessUnsetValue()
    {
        $di = new Container();
        $di->foo = 'Foo';
        unset($di->foo);

        $this->setExpectedException('Props\NotFoundException');
        $di->foo;
    }

    public function testSetFactory()
    {
        $di = new Container();
        $di->setFactory('foo', function () {
            $obj = new ContainerTestObject();
            $obj->bar = 'bar';
            return $obj;
        });

        $foo = $di->foo;

        $this->assertInstanceOf(self::TEST_CLASS, $foo);
        $this->assertEquals('bar', $foo->bar);
    }

    public function testSetValue()
    {
        $di = new Container();
        $di->setValue('foo', function () {});

        $this->assertInstanceOf('Closure', $di->foo);
    }

    /**
     * @expectedException \Props\NotFoundException
     */
    public function testCannotExtendValue()
    {
        $di = new Container();
        $di->foo = 1;
        $di->extend('foo', function ($value, Container $c) {
            return $value + 1;
        });
    }

    public function testExtend()
    {
        $di = new Container();
        $di->key = 'count';

        $di->counter = function (Container $c) {
            static $i = 0;
            $i++;
            return (object)array(
                $c->key => $i,
            );
        };

        $c1 = $di->counter; // cached with $i = 1

        $di->extend('counter', function ($value, Container $c) {
            static $i = 0;
            $i++;
            $value->one = $i;
            return $value;
        });

        $c2 = $di->counter; // because of extension, doesn't use original cached value
        $this->assertEquals((object)array('count' => 2, 'one' => 1), $c2);
        $this->assertNotSame($c1, $c2);

        $di->key = 'total';

        $c3 = $di->counter; // but caches repeat reads
        $this->assertEquals((object)array('count' => 2, 'one' => 1), $c3);
        $this->assertSame($c2, $c3);

        $c4 = $di->new_counter();
        $this->assertEquals((object)array('total' => 3, 'one' => 2), $c4);
        $this->assertNotSame($c3, $c4);

        $di->extend('counter', function ($value, Container $c) {
            static $i = 0;
            $i++;
            $value->two = $i;
            return $value;
        });

        $c5 = $di->counter; // going deep!
        $this->assertEquals((object)array('total' => 4, 'one' => 3, 'two' => 1), $c5);

        $c6 = $di->new_counter();
        $this->assertEquals((object)array('total' => 5, 'one' => 4, 'two' => 2), $c6);
    }

    /**
     * @expectedException \Props\NotFoundException
     */
    public function testGetFactoryForValue()
    {
        $di = new Container();
        $di->key = 'count';
        $di->getFactory('key');
    }

    /**
     * @expectedException \Props\NotFoundException
     */
    public function testGetMissingFactory()
    {
        $di = new Container();
        $di->getFactory('key');
    }

    public function testGetFactory()
    {
        $di = new Container();
        $factory = function () {};
        $di->foo = $factory;
        $factory2 = $di->getFactory('foo');
        $this->assertSame($factory, $factory2);
    }

    public function testGetKeys()
    {
        $di = new Container();
        $di->foo = 'foo';
        $di->bar = function () {};
        $di->bar;
        $this->assertEquals(array('foo', 'bar'), $di->getKeys());
    }
}

class ContainerTestObject
{
    public $calls;
    public $args;

    public function __construct()
    {
        $this->args = func_get_args();
    }

    public function __call($name, $args)
    {
        $this->calls[$name] = $args[0];
    }
}
