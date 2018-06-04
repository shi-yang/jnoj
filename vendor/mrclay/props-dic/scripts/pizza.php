<?php

require __DIR__ . '/../vendor/autoload.php';

class Dough {}
class Cheese {}
class Slice {}
class Pizza {
    function __construct($style, Cheese $cheese) {}
    function setDough(Dough $dough) {}
    function getSlice() { return new Slice(); }
}
class CheeseFactory {
    static function getCheese() { return new Cheese(); }
}

/**
 * @property-read string $style
 * @property-read Dough  $dough
 * @property-read Cheese $cheese
 * @property-read Pizza  $pizza
 * @method        Slice  new_slice()
 */
class MyDI extends \Props\Container {
    public function __construct() {
        $this->style = 'deluxe';

        $this->dough = function (MyContainer $c) {
            return new Dough();
        };

        $this->setFactory('cheese', 'CheeseFactory::getCheese');

        $this->pizza = function (MyContainer $c) {
            $pizza = new Pizza($c->style, $c->cheese);
            $pizza->setDough($c->dough);
            return $pizza;
        };

        // note 3rd argument $shared is false
        $this->slice = function (MyContainer $c) {
            return $c->pizza->getSlice();
        };
    }
}

$c = new MyContainer;

// You can request dependencies in any order. They're resolved as needed.

$slice1 = $c->new_slice(); // This first resolves and caches the cheese, dough, and pizza.
$slice2 = $c->new_slice(); // This just gets a new slice from the existing pizza.

assert($slice1 !== $slice2);
assert($c->pizza === $c->pizza);
