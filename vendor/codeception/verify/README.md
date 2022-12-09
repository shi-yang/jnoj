Verify
======

BDD Assertions for [PHPUnit][1] or [Codeception][2]

[![Latest Stable Version](https://poser.pugx.org/codeception/verify/v/stable)](https://packagist.org/packages/codeception/verify)
[![Total Downloads](https://poser.pugx.org/codeception/verify/downloads)](https://packagist.org/packages/codeception/verify)
[![Build Status](https://travis-ci.org/Codeception/Verify.png?branch=master)](https://travis-ci.org/Codeception/Verify)
[![License](https://poser.pugx.org/codeception/specify/license)](https://packagist.org/packages/codeception/verify)

This is very tiny wrapper for PHPUnit assertions, that are aimed to make tests a bit more readable.
With [BDD][3] assertions influenced by [Chai][4], [Jasmine][5], and [RSpec][6] your assertions would be a bit closer to natural language.

⚠️ This is the Verify 2.0 documentation, to see v1.x docs click [here.](https://github.com/Codeception/Verify/tree/1.x)

## Installation

*Requires PHP  7.4 or higher*

```
composer require codeception/verify --dev
```

> :arrow_up: **Upgrade from 1.x by following [the upgrade guide.][10]**


## Usage

Use in any test `verify` function instead of `$this->assert*` methods:

```php
use Codeception\Verify\Verify;

$user = User::find(1);

// equals
verify($user->getName())->equals('davert');

verify($user->getNumPosts())
    ->equals(5, 'user have 5 posts')
    ->notEquals(3);

// contains
Verify::Array($user->getRoles())
    ->contains('admin', 'first user is admin')
    ->notContains('banned', 'first user is not banned');


// greater / less
verify($user->getRate())
    ->greaterThan(5)
    ->lessThan(10)
    ->equals(7, 'first user rate is 7');

// true / false / null
verify($user->isAdmin())->true();
verify($user->isBanned())->false();
verify($user->invitedBy)->null();
verify($user->getPosts())->notNull();

// empty
verify($user->getComments())->empty();
verify($user->getRoles())->notEmpty();

// throws
Verify::Callable($callback)
    ->throws()
    ->throws(Exception::class)
    ->throws(Exception::class, 'exception message')
    ->throws(new Exception())
    ->throws(new Exception('message'));

// does not throw
Verify::Callable($callback)
    ->doesNotThrow()
    ->throws(Exception::class)
    ->doesNotThrow(new Exception());

// and many more !
```

> :page_facing_up: **See Verifiers full list [here.][7]**

## Alternative Syntax

If you follow TDD/BDD you'd rather use `expect` instead of `verify`:

```php
expect($user->getNumPosts())
    ->notToBeNull()
    ->toBeInt()
    ->toEqual(5, 'user have 5 posts');
```
> :page_facing_up: **See Expectations full list [here.][8]**
>
Or `verify_that` which is just an alias function:

```php
verify_that($user->getRate())->equals(7, 'first user rate is 7');
```

## Extending

In order to add more assertions you can extend the abstract class `Verify`:

```php
use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;

class MyVerify extends Verify {

    //you can type $actual to only receive a specific data type

    public function __construct($actual = null)
    {
        parent::__construct($actual);
    }

    public function success(string $message = '')
    {
        Assert::assertTrue(true, $message);
    }

}
```

And use it!

```php
$myVerify = new MyVerify;

$myVerify->success('it works!');

$myVerify::Mixed('this also')->notEquals('works');
```

## License

Verify is open-sourced software licensed under the [MIT][9] License.
© Codeception PHP Testing Framework

[1]: https://phpunit.de/
[2]: http://codeception.com/
[3]: https://en.wikipedia.org/wiki/Behavior-driven_development
[4]: http://chaijs.com/
[5]: http://jasmine.github.io/
[6]: http://rspec.info/
[7]: /docs/supported_verifiers.md
[8]: /docs/supported_expectations.md
[9]: /LICENSE
[10]: /UPGRADE.md
