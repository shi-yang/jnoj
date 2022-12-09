<?php

declare(strict_types=1);

include_once __DIR__.'/../src/Codeception/bootstrap.php';

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

final class VerifyTest extends TestCase
{
    protected DOMDocument $xml;

    protected function setUp(): void
    {
        $this->xml = new DOMDocument;
        $this->xml->loadXML('<foo><bar>Baz</bar><bar>Baz</bar></foo>');
    }

    public function testEquals(): void
    {
        verify(5)->equals(5);
        verify('hello')->equals('hello');
        verify(5)->equals(5, 'user have 5 posts');
        verify(3.251)->equalsWithDelta(3.25, 0.01);
        verify(3.251)->equalsWithDelta(3.25, 0.01, 'respects delta');
        verify(__FILE__)->fileEquals(__FILE__);
    }

    public function testNotEquals(): void
    {
        verify(3)->notEquals(5);
        verify(3.252)->notEqualsWithDelta(3.25, 0.001);
        verify(3.252)->notEqualsWithDelta(3.25, 0.001, 'respects delta');
        verify(__FILE__)->fileNotEquals(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'composer.json');
    }

    public function testContains(): void
    {
        verify([3, 2])->arrayContains(3);
        verify([3, 2])->arrayNotContains(5, 'user have 5 posts');
    }

    public function testGreaterLowerThan(): void
    {
        verify(7)->greaterThan(5);
        verify(7)->lessThan(10);
        verify(7)->lessThanOrEqual(7);
        verify(7)->lessThanOrEqual(8);
        verify(7)->greaterThanOrEqual(7);
        verify(7)->greaterThanOrEqual(5);
    }

    public function testTrueFalseNull(): void
    {
        verify(true)->true();
        verify(false)->false();
        verify(null)->null();
        verify(true)->notNull();
        verify(false)->false('something should be false');
        verify(true)->true('something should be true');
    }

    public function testEmptyNotEmpty(): void
    {
        verify(array('3', '5'))->notEmpty();
        verify(array())->empty();
    }

    public function testArrayHasKey(): void
    {
        $errors = ['title' => 'You should add title'];
        verify($errors)->arrayHasKey('title');
        verify($errors)->arrayHasNotKey('body');
    }

    public function testIsInstanceOf(): void
    {
        $testClass = new DateTime();
        verify($testClass)->instanceOf(DateTime::class);
        verify($testClass)->notInstanceOf(DateTimeZone::class);
    }

    public function testHasAttribute(): void
    {
        verify('Exception')->classHasAttribute('message');
        verify('Exception')->classNotHasAttribute('fakeproperty');

        $testObject = (object) ['existingAttribute' => true];
        verify($testObject)->baseObjectHasAttribute('existingAttribute');
        verify($testObject)->baseObjectNotHasAttribute('fakeproperty');
    }

    public function testHasStaticAttribute(): void
    {
        verify('FakeClassForTesting')->classHasStaticAttribute('staticProperty');
        verify('FakeClassForTesting')->classNotHasStaticAttribute('fakeProperty');
    }

    public function testContainsOnly(): void
    {
        verify(['1', '2', '3'])->arrayContainsOnly('string');
        verify(['1', '2', 3])->arrayNotContainsOnly('string');
    }

    public function testContainsOnlyInstancesOf(): void
    {
        verify([new FakeClassForTesting(), new FakeClassForTesting(), new FakeClassForTesting()])
            ->arrayContainsOnlyInstancesOf('FakeClassForTesting');
    }

    public function testCount(): void
    {
        verify([1, 2, 3])->arrayCount(3);
        verify([1, 2, 3])->arrayNotCount(2);
    }

    public function testFileExists(): void
    {
        verify(__FILE__)->fileExists();
        verify('completelyrandomfilename.txt')->fileDoesNotExists();
    }

    public function testEqualsJsonFile(): void
    {
        verify(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'json-test-file.json')
            ->jsonFileEqualsJsonFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'equal-json-test-file.json');
        verify('{"some" : "data"}')->jsonStringEqualsJsonFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'equal-json-test-file.json');
    }

    public function testEqualsJsonString(): void
    {
        verify('{"some" : "data"}')->jsonStringEqualsJsonString('{"some" : "data"}');
    }

    public function testRegExp(): void
    {
        verify('somestring')->stringMatchesRegExp('/string/');
    }

    public function testMatchesFormat(): void
    {
        verify('somestring')->stringMatchesFormat('%s');
        verify('somestring')->stringNotMatchesFormat('%i');
    }

    public function testMatchesFormatFile(): void
    {
        verify('23')->stringMatchesFormatFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'format-file.txt');
        verify('asdfas')->stringNotMatchesFormatFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'format-file.txt');
    }

    public function testSame(): void
    {
        verify(1)->same(0+1);
        verify(1)->notSame(true);
    }

    public function testEndsWith(): void
    {
        verify('A completely not funny string')->stringEndsWith('ny string');
        verify('A completely not funny string')->stringNotEndsWith('A completely');
    }

    public function testEqualsFile(): void
    {
        verify('%i')->stringEqualsFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'format-file.txt');
        verify('Another string')->stringNotEqualsFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'format-file.txt');
    }

    public function testStartsWith(): void
    {
        verify('A completely not funny string')->stringStartsWith('A completely');
        verify('A completely not funny string')->stringStartsNotWith('string');
    }

    public function testEqualsXmlFile(): void
    {
        verify(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'xml-test-file.xml')
            ->xmlFileEqualsXmlFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'xml-test-file.xml');
        verify('<foo><bar>Baz</bar><bar>Baz</bar></foo>')
            ->xmlStringEqualsXmlFile(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'xml-test-file.xml');
    }

    public function testEqualsXmlString(): void
    {
        verify('<foo><bar>Baz</bar><bar>Baz</bar></foo>')
            ->xmlStringEqualsXmlString('<foo><bar>Baz</bar><bar>Baz</bar></foo>');
    }

    public function testStringContainsString(): void
    {
        verify('foo bar')->stringContainsString('o b');
        verify('foo bar')->stringNotContainsString('BAR');
    }

    public function testStringContainsStringIgnoringCase(): void
    {
        verify('foo bar')->stringContainsStringIgnoringCase('O b');
        verify('foo bar')->stringNotContainsStringIgnoringCase('baz');
    }

    public function testIsString(): void
    {
        verify('foo bar')->isString();
        verify(false)->isNotString();
    }

    public function testIsArray(): void
    {
        verify([1,2,3])->isArray();
        verify(false)->isNotArray();
    }

    public function testIsBool(): void
    {
        verify(false)->isBool();
        verify([1,2,3])->isNotBool();
    }

    public function testIsFloat(): void
    {
        verify(1.5)->isFloat();
        verify(1)->isNotFloat();
    }

    public function testIsInt(): void
    {
        verify(5)->isInt();
        verify(1.5)->isNotInt();
    }

    public function testIsNumeric(): void
    {
        verify('1.5')->isNumeric();
        verify('foo bar')->isNotNumeric();
    }

    public function testIsObject(): void
    {
        verify(new stdClass)->isObject();
        verify(false)->isNotObject();
    }

    public function testIsResource(): void
    {
        verify(fopen(__FILE__, 'r'))->isResource();
        verify(false)->isNotResource();
    }

    public function testIsScalar(): void
    {
        verify('foo bar')->isScalar();
        verify([1,2,3])->isNotScalar();
    }

    public function testIsCallable(): void
    {
        verify(function(): void {})->isCallable();
        verify(false)->isNotCallable();
    }

    public function testEqualsCanonicalizing(): void
    {
        verify([3, 2, 1])->equalsCanonicalizing([1, 2, 3]);
    }

    public function testNotEqualsCanonicalizing(): void
    {
        verify([3, 2, 1])->notEqualsCanonicalizing([2, 3, 0, 1]);
    }

    public function testEqualsIgnoringCase(): void
    {
        verify('foo')->equalsIgnoringCase('FOO');
    }

    public function testNotEqualsIgnoringCase(): void
    {
        verify('foo')->notEqualsIgnoringCase('BAR');
    }

    public function testEqualsWithDelta(): void
    {
        verify(1.01)->equalsWithDelta(1.0, 0.1);
    }

    public function testNotEqualsWithDelta(): void
    {
        verify(1.2)->notEqualsWithDelta(1.0, 0.1);
    }

    public function testThrows(): void
    {
        $func = function (): void {
            throw new Exception('foo');
        };

        verify($func)->callableThrows();
        verify($func)->callableThrows(Exception::class);
        verify($func)->callableThrows(Exception::class, 'foo');
        verify($func)->callableThrows(new Exception());
        verify($func)->callableThrows(new Exception('foo'));

        verify(function () use ($func): void {
            verify($func)->callableThrows(RuntimeException::class);
        })->callableThrows(ExpectationFailedException::class);

        verify(function (): void {
            verify(function (): void {})->callableThrows(Exception::class);
        })->callableThrows(new ExpectationFailedException("exception 'Exception' was not thrown as expected"));
    }

    public function testDoesNotThrow(): void
    {
        $func = function (): void {
            throw new Exception('foo');
        };

        verify(function (): void {})->callableDoesNotThrow();
        verify($func)->callableDoesNotThrow(RuntimeException::class);
        verify($func)->callableDoesNotThrow(RuntimeException::class, 'bar');
        verify($func)->callableDoesNotThrow(RuntimeException::class, 'foo');
        verify($func)->callableDoesNotThrow(new RuntimeException());
        verify($func)->callableDoesNotThrow(new RuntimeException('bar'));
        verify($func)->callableDoesNotThrow(new RuntimeException('foo'));
        verify($func)->callableDoesNotThrow(Exception::class, 'bar');
        verify($func)->callableDoesNotThrow(new Exception('bar'));

        verify(function () use ($func): void {
            verify($func)->callableDoesNotThrow();
        })->callableThrows(new ExpectationFailedException('exception was not expected to be thrown'));

        verify(function () use ($func): void {
            verify($func)->callableDoesNotThrow(Exception::class);
        })->callableThrows(new ExpectationFailedException("exception 'Exception' was not expected to be thrown"));

        verify(function () use ($func): void {
            verify($func)->callableDoesNotThrow(Exception::class, 'foo');
        })->callableThrows(new ExpectationFailedException("exception 'Exception' with message 'foo' was not expected to be thrown"));
    }
}


class FakeClassForTesting
{
    static $staticProperty;
}
