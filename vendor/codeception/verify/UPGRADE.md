UPGRADE FROM 1.X TO 2.X
=======================


PHP version
------

 * Removed support for `PHP 7.1` & `PHP 7.2`.


Verify function
-------

In version `2.x`, `verifiers` can be used as classes. Each verifier class handles a specific type of data.

Thanks to this you can enjoy an autocompletion of your `IDE` much more intelligent than before...

That is why **we remove some global functions** that have a less intuitive behavior.

According to the above:

 * `verify` no longer receives a `string $message` as a parameter, now each _**verifier**_ fulfills this function.
 * `verify_not` was deleted. Use `verify()->empty` instead.
 * `expect_that` and `expect_not` were deleted. Use `expect()->notEmpty` and `expect()->empty` instead.
 * `expect_file` and `setIsFileExpectation` were deleted. Use `Verify::File()` instead.

Verifiers
-------

|                  Verify 1.x                     |                   Verify 2.x                    |
|-------------------------------------------------|-------------------------------------------------|
| `verify()->array`                               | `verify()->isArray`                             |
| `verify()->bool`                                | `verify()->isBool`                              |
| `verify()->callable`                            | `verify()->isCallable`                          |
| `verify()->float`                               | `verify()->isFloat`                             |
| `verify()->greaterOrEquals`                     | `verify()->greaterThanOrEqual`                  |
| `verify()->int`                                 | `verify()->isInt`                               |
| `verify()->isEmpty`                             | `verify()->empty`                               |
| `verify()->isInstanceOf`                        | `verify()->instanceOf`                          |
| `verify()->isNotInstanceOf`                     | `verify()->notInstanceOf`                       |
| `verify()->lessOrEquals`                        | `verify()->lessThanOrEqual`                     |
| `verify()->notArray`                            | `verify()->isNotArray`                          |
| `verify()->notBool`                             | `verify()->isNotBool`                           |
| `verify()->notCallable`                         | `verify()->isNotCallable`                       |
| `verify()->notFloat`                            | `verify()->isNotFloat`                          |
| `verify()->notInt`                              | `verify()->isNotInt`                            |
| `verify()->notNumeric`                          | `verify()->isNotNumeric`                        |
| `verify()->notObject`                           | `verify()->isNotObject`                         |
| `verify()->notResource`                         | `verify()->isNotResource`                       |
| `verify()->notScalar`                           | `verify()->isNotScalar`                         |
| `verify()->notString`                           | `verify()->isNotString`                         |
| `verify()->numeric`                             | `verify()->isNumeric`                           |
| `verify()->object`                              | `verify()->isObject`                            |
| `verify()->resource`                            | `verify()->isResource`                          |
| `verify()->scalar`                              | `verify()->isScalar`                            |
| `verify()->string`                              | `verify()->isString`                            |
| `verify()->hasAttribute`                        | `Verify()->baseObjectHasAttribute`              |
| `verify()->notHasAttribute`                     | `Verify()->baseObjectNotHasAttribute`           |
| `verify()->throws`                              | `Verify()->callableThrows`                      |
| `verify()->doesNotThrow`                        | `Verify()->callableDoesNotThrow`                |
| `verify()->hasStaticAttribute`                  | `Verify()->classHasStaticAttribute`             |
| `verify()->notHasStaticAttribute`               | `Verify()->classNotHasStaticAttribute`          |
| `verify()->hasAttribute`                        | `Verify()->classHasAttribute`                   |
| `verify()->notHasAttribute`                     | `Verify()->classNotHasAttribute`                |
| `verify()->notExists`                           | `Verify()->fileDoesNotExists`                   |
| `verify()->regExp`                              | `Verify()->stringMatchesRegExp`                 |
| `verify()->notRegExp`                           | `Verify()->stringDoesNotMatchRegExp`            |
| `verify()->notStartsWith`                       | `Verify()->stringNotStartsWith`                 |


Extending
-------

 * `Codeception\Verify::$override` was removed, extend from abstract `Codeception\Verify\Verify` class instead.
