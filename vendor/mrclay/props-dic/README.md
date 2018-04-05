# Props [![Build Status](https://travis-ci.org/mrclay/Props.png)](https://travis-ci.org/mrclay/Props)

Most [Dependency Injection](http://www.mrclay.org/2014/04/06/dependency-injection-ask-for-what-you-need/) containers have fetch operations, like `$di->get('foo')` or `$di['foo']`, which don't allow your IDE to know the type of value received, nor offer you any help remembering/typing key names.

With **Props**, you access values via custom property reads `$di->foo` or method calls `$di->new_foo()`. This allows you to subclass the container and provide `@property` and/or `@method` PHPDoc declarations, giving your IDE and static analysis tools valuable runtime type information.

An example will help:

```php
/**
 * @property-read Foo $foo
 * @method        Foo new_foo()
 */
class MyContainer extends \Props\Container {
    public function __construct() {
        $this->foo = function (MyContainer $c) {
            return new Foo();
        };
    }
}

$c = new MyContainer();

$foo1 = $c->foo; // your IDE knows this is a Foo instance

$foo2 = $c->new_foo(); // A fresh Foo instance

$foo3 = $c->foo; // same as $foo1
```

Here's a more complex example:

```php
/**
 * @property-read string $style
 * @property-read Dough  $dough
 * @property-read Cheese $cheese
 * @property-read Pizza  $pizza
 * @method        Slice  new_slice()
 */
class PizzaServices extends \Props\Container {
    public function __construct() {
        $this->style = 'deluxe';

        $this->dough = function (PizzaServices $c) {
            return new Dough();
        };

        $this->setFactory('cheese', 'CheeseFactory::getCheese');

        $this->pizza = function (PizzaServices $c) {
            $pizza = new Pizza($c->style, $c->cheese);
            $pizza->setDough($c->dough);
            return $pizza;
        };

        $this->slice = function (PizzaServices $c) {
            return $c->pizza->getSlice();
        };
    }
}

$c = new PizzaServices;

$c->pizza; // This first resolves and caches the cheese and dough.

$c->pizza; // The same pizza instance as above (no factories called).
```

Since "slice" has a factory function set, we can call `new_slice()` to get fresh instances from it:

```php
$c->new_slice(); // a new Slice instance
$c->new_slice(); // a new Slice instance
```

Your IDE sees the container as a plain old class of typed properties, allowing it to offer suggestions of available properties, autocomplete their names, and autocomplete the objects returned. It gives you much more power when providing static analysis and automated refactoring.

## Compatibility

`Props\Container` implements [`ContainerInterface`](https://github.com/container-interop/container-interop).

## Overview

You can specify dependencies via direct setting:

```php
$c->aaa = new AAA();
```

You can specify factories by setting a `Closure`, or by using the `setFactory()` method. These are functionally equivalent:

```php
$c->bbb = function ($c) {
    return BBB::factory($c);
};

$c->setFactory('bbb', 'BBB::factory');
```

Resolved dependencies are cached, returning the same instance:

```php
$c->bbb === $c->bbb; // true
```

### Using factories

If you don't want a cached value, use `new_PROPERTYNAME()` to always fetch a fresh instance:

```php
$c->new_bbb() === $c->new_bbb(); // false
```

Regular value sets do not store a factory, so you may want to check `hasFactory()` before you use `new_PROPERTYNAME()`:

```php
// store a value
$c->ccc = new CCC();
$c->hasFactory('ccc'); // false

// store a factory
$c->ccc = function () {
    return new CCC();
};
$c->hasFactory('ccc'); // true
```

You can also get access to a set factory:

```php
$callable = $c->getFactory('ccc');
```

### Extending a factory

Use `extend` to have the return value of a factory filtered before it's returned:

```php
$c->foo = function ($c) {
    return new Foo($c->bar);
};

$c->extend('foo', function ($value, Container $c) {
    return array($value, $c->bing);
});

$c->foo; // [Foo, "bing"]

$c->new_foo(); // re-call original foo factory and re-extend output (`bar` and `bing` will be re-read)
```

## Pimple with property access

If you're used to the [Pimple](http://pimple.sensiolabs.org/) API, try `Props\Pimple`, which just adds property access. With that you can add `@property` declarations and get the same typing benefits.

You can see an [example](https://github.com/mrclay/Props/blob/master/scripts/example-pimple.php) that's similar to the Pimple docs.

## Requirements

 * PHP 5.3

### License (MIT)

See [LICENSE](https://github.com/mrclay/Props/blob/master/src/LICENSE).
