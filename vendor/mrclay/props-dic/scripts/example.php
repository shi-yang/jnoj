<?php

require __DIR__ . '/../vendor/autoload.php';

class AAA {}
class BBB {}
class CCC {
    public function __construct(BBB $bbb) {}
    public function setBbb(BBB $bbb) {}
    public $aaa;
}
class DDD {}
function get_a_bbb() { return new BBB; }

/**
 * @property-read AAA $aaa
 * @property-read BBB $bbb1
 * @property-read BBB $bbb2
 * @property-read BBB $bbb3
 * @property-read CCC $ccc
 * @property-read DDD $ddd
 *
 * @method AAA new_aaa()
 */
class MyContainer extends \Props\Container {
    public function __construct() {
        // store plain old values
        $this->ddd = new DDD;
        $this->{'bbb.class'} = 'BBB';

        // set a factory, which will construct an object on demand
        $this->aaa = function () {
            return new AAA();
        };

        // alternative factory syntax, and using a reference to specify the class name
        $this->setFactory('bbb1', function (MyContainer $c) {
            return new $c->{'bbb.class'};
        });

        // fetch with a callback
        $this->setFactory('bbb2', 'get_a_bbb');

        // Closures automatically used as factories
        $this->bbb3 = function (MyContainer $c) {
            return $c->bbb2;
        };

        // more advanced factory
        $this->ccc = function (MyContainer $c) {
            $val = new CCC($c->bbb1);
            $val->setBbb($c->bbb2);
            $val->aaa = $c->aaa;
            return $val;
        };
    }
}

$c = new MyContainer;

$c->aaa; // factory builds a AAA
$c->aaa; // the same AAA
$c->new_aaa(); // always a freshly-built AAA

$c->bbb1; // factory resolves bar.class, builds a BBB
$c->bbb2; // invoker calls get_a_bbb()
$c->bbb3; // invoker executes anon func, returning the already-cached $c->bbb2 instance

$c->ccc; // factory creates CCC, passing a new BBB object,
          // calls setBbb(), passing in $c->bbb2,
          // and sets the aaa property to $c->aaa
