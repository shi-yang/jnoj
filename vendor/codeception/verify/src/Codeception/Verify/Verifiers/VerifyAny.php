<?php

declare(strict_types=1);

namespace Codeception\Verify\Verifiers;

use Codeception\Verify\Verify;

class VerifyAny extends VerifyMixed
{
    public function arrayContains($needle, string $message = ''): self
    {
        Verify::Array($this->actual)->contains($needle, $message);
        return $this;
    }

    public function arrayContainsEquals($needle, string $message = ''): self
    {
        Verify::Array($this->actual)->containsEquals($needle, $message);
        return $this;
    }

    public function arrayContainsOnly(string $type, ?bool $isNativeType = null, string $message = ''): self
    {
        Verify::Array($this->actual)->containsOnly($type, $isNativeType, $message);
        return $this;
    }

    public function arrayContainsOnlyInstancesOf(string $className, string $message = ''): self
    {
        Verify::Array($this->actual)->containsOnlyInstancesOf($className, $message);
        return $this;
    }

    public function arrayCount(int $expectedCount, string $message = ''): self
    {
        Verify::Array($this->actual)->count($expectedCount, $message);
        return $this;
    }

    public function arrayHasKey($key, string $message = ''): self
    {
        Verify::Array($this->actual)->hasKey($key, $message);
        return $this;
    }

    public function arrayHasNotKey($key, string $message = ''): self
    {
        Verify::Array($this->actual)->hasNotKey($key, $message);
        return $this;
    }

    public function arrayNotContains($needle, string $message = ''): self
    {
        Verify::Array($this->actual)->notContains($needle, $message);
        return $this;
    }

    public function arrayNotContainsEquals($needle, string $message = ''): self
    {
        Verify::Array($this->actual)->notContainsEquals($needle, $message);
        return $this;
    }

    public function arrayNotContainsOnly(string $type, ?bool $isNativeType = null, string $message = ''): self
    {
        Verify::Array($this->actual)->notContainsOnly($type, $isNativeType, $message);
        return $this;
    }

    public function arrayNotCount(int $expectedCount, string $message = ''): self
    {
        Verify::Array($this->actual)->notCount($expectedCount, $message);
        return $this;
    }

    public function arrayNotSameSize($expected, string $message = ''): self
    {
        Verify::Array($this->actual)->notSameSize($expected, $message);
        return $this;
    }

    public function arraySameSize($expected, string $message = ''): self
    {
        Verify::Array($this->actual)->sameSize($expected, $message);
        return $this;
    }

    public function baseObjectHasAttribute(string $attributeName, string $message = ''): self
    {
        Verify::BaseObject($this->actual)->hasAttribute($attributeName, $message);
        return $this;
    }

    public function baseObjectNotHasAttribute(string $attributeName, string $message = ''): self
    {
        Verify::BaseObject($this->actual)->notHasAttribute($attributeName, $message);
        return $this;
    }

    public function callableThrows($throws = null, string $message = ''): self
    {
        Verify::Callable($this->actual)->throws($throws, $message);
        return $this;
    }

    public function callableDoesNotThrow($throws = null, string $message = ''): self
    {
        Verify::Callable($this->actual)->doesNotThrow($throws, $message);
        return $this;
    }

    public function classHasAttribute(string $attributeName, string $message = ''): self
    {
        Verify::Class($this->actual)->hasAttribute($attributeName, $message);
        return $this;
    }

    public function classHasStaticAttribute(string $attributeName, string $message = ''): self
    {
        Verify::Class($this->actual)->hasStaticAttribute($attributeName, $message);
        return $this;
    }

    public function classNotHasAttribute(string $attributeName, string $message = ''): self
    {
        Verify::Class($this->actual)->notHasAttribute($attributeName, $message);
        return $this;
    }

    public function classNotHasStaticAttribute(string $attributeName, string $message = ''): self
    {
        Verify::Class($this->actual)->notHasStaticAttribute($attributeName, $message);
        return $this;
    }

    public function directoryDoesNotExist(string $message = ''): self
    {
        Verify::Directory($this->actual)->doesNotExist($message);
        return $this;
    }

    public function directoryExists(string $message = ''): self
    {
        Verify::Directory($this->actual)->exists($message);
        return $this;
    }

    public function directoryIsNotReadable(string $message = ''): self
    {
        Verify::Directory($this->actual)->isNotReadable($message);
        return $this;
    }

    public function directoryIsNotWritable(string $message = ''): self
    {
        Verify::Directory($this->actual)->isNotWritable($message);
        return $this;
    }

    public function directoryIsReadable(string $message = ''): self
    {
        Verify::Directory($this->actual)->isReadable($message);
        return $this;
    }

    public function directoryIsWritable(string $message = ''): self
    {
        Verify::Directory($this->actual)->isWritable($message);
        return $this;
    }

    public function fileDoesNotExists(string $message = ''): self
    {
        Verify::File($this->actual)->doesNotExists($message);
        return $this;
    }

    public function fileEquals(string $expected, string $message = ''): self
    {
        Verify::File($this->actual)->equals($expected, $message);
        return $this;
    }

    public function fileEqualsCanonicalizing(string $expected, string $message = ''): self
    {
        Verify::File($this->actual)->equalsCanonicalizing($expected, $message);
        return $this;
    }

    public function fileEqualsIgnoringCase(string $expected, string $message = ''): self
    {
        Verify::File($this->actual)->equalsIgnoringCase($expected, $message);
        return $this;
    }

    public function fileExists(string $message = ''): self
    {
        Verify::File($this->actual)->exists($message);
        return $this;
    }

    public function fileIsNotReadable(string $message = ''): self
    {
        Verify::File($this->actual)->isNotReadable($message);
        return $this;
    }

    public function fileIsNotWritable(string $message = ''): self
    {
        Verify::File($this->actual)->isNotWritable($message);
        return $this;
    }

    public function fileIsReadable(string $message = ''): self
    {
        Verify::File($this->actual)->isReadable($message);
        return $this;
    }

    public function fileIsWritable(string $message = ''): self
    {
        Verify::File($this->actual)->isWritable($message);
        return $this;
    }

    public function fileNotEquals(string $expected, string $message = ''): self
    {
        Verify::File($this->actual)->notEquals($expected, $message);
        return $this;
    }

    public function fileNotEqualsCanonicalizing(string $expected, string $message = ''): self
    {
        Verify::File($this->actual)->notEqualsCanonicalizing($expected, $message);
        return $this;
    }

    public function fileNotEqualsIgnoringCase(string $expected, string $message = ''): self
    {
        Verify::File($this->actual)->notEqualsIgnoringCase($expected, $message);
        return $this;
    }

    public function jsonFileEqualsJsonFile(string $expectedFile, string $message = ''): self
    {
        Verify::JsonFile($this->actual)->equalsJsonFile($expectedFile, $message);
        return $this;
    }

    public function jsonFileNotEqualsJsonFile(string $expectedFile, string $message = ''): self
    {
        Verify::JsonFile($this->actual)->notEqualsJsonFile($expectedFile, $message);
        return $this;
    }

    public function jsonStringEqualsJsonFile(string $expectedFile, string $message = ''): self
    {
        Verify::JsonString($this->actual)->equalsJsonFile($expectedFile, $message);
        return $this;
    }

    public function jsonStringEqualsJsonString(string $expectedJson, string $message = ''): self
    {
        Verify::JsonString($this->actual)->equalsJsonString($expectedJson, $message);
        return $this;
    }

    public function jsonStringNotEqualsJsonFile(string $expectedFile, string $message = ''): self
    {
        Verify::JsonString($this->actual)->notEqualsJsonFile($expectedFile, $message);
        return $this;
    }

    public function jsonStringNotEqualsJsonString(string $expectedJson, string $message = ''): self
    {
        Verify::JsonString($this->actual)->notEqualsJsonString($expectedJson, $message);
        return $this;
    }

    public function stringContainsString(string $needle, string $message = ''): self
    {
        Verify::String($this->actual)->containsString($needle, $message);
        return $this;
    }

    public function stringContainsStringIgnoringCase($needle, string $message = ''): self
    {
        Verify::String($this->actual)->containsStringIgnoringCase($needle, $message);
        return $this;
    }

    public function stringDoesNotMatchRegExp(string $pattern, string $message = ''): self
    {
        Verify::String($this->actual)->doesNotMatchRegExp($pattern, $message);
        return $this;
    }

    public function stringEndsWith(string $suffix, string $message = ''): self
    {
        Verify::String($this->actual)->endsWith($suffix, $message);
        return $this;
    }

    public function stringEqualsFile(string $expectedFile, string $message = ''): self
    {
        Verify::String($this->actual)->equalsFile($expectedFile, $message);
        return $this;
    }

    public function stringEqualsFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Verify::String($this->actual)->equalsFileCanonicalizing($expectedFile, $message);
        return $this;
    }

    public function stringEqualsFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Verify::String($this->actual)->equalsFileIgnoringCase($expectedFile, $message);
        return $this;
    }

    public function stringJson(string $message = ''): self
    {
        Verify::String($this->actual)->json($message);
        return $this;
    }

    public function stringMatchesFormat(string $format, string $message = ''): self
    {
        Verify::String($this->actual)->matchesFormat($format, $message);
        return $this;
    }

    public function stringMatchesFormatFile(string $formatFile, string $message = ''): self
    {
        Verify::String($this->actual)->matchesFormatFile($formatFile, $message);
        return $this;
    }

    public function stringMatchesRegExp(string $pattern, string $message = ''): self
    {
        Verify::String($this->actual)->matchesRegExp($pattern, $message);
        return $this;
    }

    public function stringNotContainsString(string $needle, string $message = ''): self
    {
        Verify::String($this->actual)->notContainsString($needle, $message);
        return $this;
    }

    public function stringNotContainsStringIgnoringCase(string $needle, string $message = ''): self
    {
        Verify::String($this->actual)->notContainsStringIgnoringCase($needle, $message);
        return $this;
    }

    public function stringNotEndsWith(string $suffix, string $message = ''): self
    {
        Verify::String($this->actual)->notEndsWith($suffix, $message);
        return $this;
    }

    public function stringNotEqualsFile(string $expectedFile, string $message = ''): self
    {
        Verify::String($this->actual)->notEqualsFile($expectedFile, $message);
        return $this;
    }

    public function stringNotEqualsFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Verify::String($this->actual)->notEqualsFileCanonicalizing($expectedFile, $message);
        return $this;
    }

    public function stringNotEqualsFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Verify::String($this->actual)->notEqualsFileIgnoringCase($expectedFile, $message);
        return $this;
    }

    public function stringNotMatchesFormat($format, string $message = ''): self
    {
        Verify::String($this->actual)->notMatchesFormat($format, $message);
        return $this;
    }

    public function stringNotMatchesFormatFile(string $formatFile, string $message = ''): self
    {
        Verify::String($this->actual)->notMatchesFormatFile($formatFile, $message);
        return $this;
    }

    public function stringStartsNotWith(string $prefix, string $message = ''): self
    {
        Verify::String($this->actual)->startsNotWith($prefix, $message);
        return $this;
    }

    public function stringStartsWith(string $prefix, string $message = ''): self
    {
        Verify::String($this->actual)->startsWith($prefix, $message);
        return $this;
    }

    public function xmlFileEqualsXmlFile(string $expectedFile, string $message = ''): self
    {
        Verify::XmlFile($this->actual)->equalsXmlFile($expectedFile, $message);
        return $this;
    }

    public function xmlFileNotEqualsXmlFile(string $expectedFile, string $message = ''): self
    {
        Verify::XmlFile($this->actual)->notEqualsXmlFile($expectedFile, $message);
        return $this;
    }

    public function xmlStringEqualsXmlFile(string $expectedFile, string $message = ''): self
    {
        Verify::XmlString($this->actual)->equalsXmlFile($expectedFile, $message);
        return $this;
    }

    public function xmlStringEqualsXmlString($expectedXml, string $message = ''): self
    {
        Verify::XmlString($this->actual)->equalsXmlString($expectedXml, $message);
        return $this;
    }

    public function xmlStringNotEqualsXmlFile(string $expectedFile, string $message = ''): self
    {
        Verify::XmlString($this->actual)->notEqualsXmlFile($expectedFile, $message);
        return $this;
    }

    public function xmlStringNotEqualsXmlString($expectedXml, string $message = ''): self
    {
        Verify::XmlString($this->actual)->notEqualsXmlString($expectedXml, $message);
        return $this;
    }
}
