<?php
/**
 * Example of Props\Pimple based on official Pimple docs
 */

namespace {
    require __DIR__ . '/../vendor/autoload.php';
}

namespace PropsExample {

    class SessionStorage {
        public function __construct($cookieName) { $this->cookieName = $cookieName; }
    }
    class Session {
        public function __construct($storage) { $this->storage = $storage; }
    }
    class Zend_Mail {
        public function setFrom($from) { $this->from = $from; }
    }

    /**
     * @property-read string     $cookie_name
     * @property-read string     $session_storage_class
     * @property-read Session   $session
     * @property-read \Closure   $random
     * @property-read Zend_Mail $mail
     */
    class MyContainer2 extends \Props\Pimple {
        public function __construct() {
            parent::__construct();

            $this->cookie_name = 'SESSION_ID';

            $this->session_storage_class = 'PropsExample\\SessionStorage';

            $this->session_storage = function (MyContainer2 $c) {
                $class = $c->session_storage_class;
                return new $class($c->cookie_name);
            };

            $this->session = $this->factory(function (MyContainer2 $c) {
                return new Session($c->session_storage);
            });

            $this->random = $this->protect(function () { return rand(); });

            $this->mail = function (MyContainer2 $c) {
                return new Zend_Mail();
            };

            $this->{'mail.default_from'} = 'foo@example.com';

            $this->extend('mail', function($mail, MyContainer2 $c) {
                $mail->setFrom($c->{'mail.default_from'});
                return $mail;
            });
        }
    }

    $c = new MyContainer2;

    $r1 = $c->random;
    $r2 = $c->random;

    echo (int)($r1 === $r2) . "<br>";

    echo $r1() . "<br>";

    echo get_class($c->raw('session')) . '<br>';

    echo var_export($c->session, true) . '<br>';

    echo var_export($c->mail, true) . '<br>';
}
