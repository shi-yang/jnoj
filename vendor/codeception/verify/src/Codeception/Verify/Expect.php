<?php

declare(strict_types=1);

namespace Codeception\Verify;

use ArrayAccess;
use Codeception\Verify\Expectations\ExpectArray;
use Codeception\Verify\Expectations\ExpectBaseObject;
use Codeception\Verify\Expectations\ExpectCallable;
use Codeception\Verify\Expectations\ExpectClass;
use Codeception\Verify\Expectations\ExpectDirectory;
use Codeception\Verify\Expectations\ExpectFile;
use Codeception\Verify\Expectations\ExpectJsonFile;
use Codeception\Verify\Expectations\ExpectJsonString;
use Codeception\Verify\Expectations\ExpectString;
use Codeception\Verify\Expectations\ExpectXmlFile;
use Codeception\Verify\Expectations\ExpectXmlString;
use Countable;

abstract class Expect
{
    /** @var mixed */
    protected $actual = null;

    /**
     * Expect constructor
     *
     * @param mixed $actual
     */
    protected function __construct($actual)
    {
        $this->actual = $actual;
    }

    /**
     * @param mixed $actual
     * @return self
     */
    public function __invoke($actual): self
    {
        return $this($actual);
    }

    public static function File(string $filename): ExpectFile
    {
        return new ExpectFile($filename);
    }

    public static function JsonFile(string $filename): ExpectJsonFile
    {
        return new ExpectJsonFile($filename);
    }

    public static function JsonString(string $json): ExpectJsonString
    {
        return new ExpectJsonString($json);
    }

    public static function XmlFile(string $filename): ExpectXmlFile
    {
        return new ExpectXmlFile($filename);
    }

    public static function XmlString(string $xml): ExpectXmlString
    {
        return new ExpectXmlString($xml);
    }

    public static function BaseObject(object $object): ExpectBaseObject
    {
        return new ExpectBaseObject($object);
    }

    public static function Class(string $className): ExpectClass
    {
        return new ExpectClass($className);
    }

    public static function Directory(string $directory): ExpectDirectory
    {
        return new ExpectDirectory($directory);
    }

    /**
     * @param array|ArrayAccess|Countable|iterable $array
     * @return ExpectArray
     */
    public static function Array($array): ExpectArray
    {
        return new ExpectArray($array);
    }

    public static function String(string $string): ExpectString
    {
        return new ExpectString($string);
    }

    public static function Callable(callable $callable): ExpectCallable
    {
        return new ExpectCallable($callable);
    }

    /**
     * @param mixed $actual
     * @return ExpectCallable
     */
    public static function Mixed($actual): ExpectCallable
    {
        return new ExpectCallable($actual);
    }
}