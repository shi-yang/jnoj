<?php

namespace Tests\JSMin;

use JSMin\JSMin;

/**
 * Copyright (c) 2009, Robert Hafner
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * Neither the name of the Stash Project nor the
 * names of its contributors may be used to endorse or promote products
 * derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Robert Hafner BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */
class JSMinTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @group minify
	 * @dataProvider minifyProvider
	 */
	public function testMinify($testName, $input, $expected, $actualFile)
	{
		$actual = JSMin::minify($input);
		if ($actual !== $expected && is_writable(dirname($actualFile))) {
			file_put_contents($actualFile, $actual);
		}
		$this->assertEquals($expected, $actual, 'Running Minify Test: ' . $testName);
	}

	public function testWhitespace() {
		$this->assertEquals("hello;", JSMin::minify("\r\n\r\nhello;\r\n"));
	}

	public function testBomRemoval() {
        $this->assertEquals("hello;", JSMin::minify("\xEF\xBB\xBFhello;"));
	}

    public function testFuncOverload() {
        if (!function_exists('mb_strlen') || !((int)ini_get('mbstring.func_overload') & 2)) {
            $this->markTestIncomplete('Cannot be tested unless mbstring.func_overload is used');
            return;
        }

        $input = 'function(s) {  return /^[£$€?.]/.test(s); }';
        $expected = 'function(s){return/^[£$€?.]/.test(s);}';
        $this->assertEquals($expected, JSMin::minify($input));
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testExpections($input, $label, $expClass, $expMessage) {
        $eClass = $eMsg = '';
        try {
            JSMin::minify($input);
        } catch (\Exception $e) {
            $eClass = get_class($e);
            $eMsg = $e->getMessage();
        }
        $this->assertTrue(
            $eClass === $expClass && $eMsg === $expMessage,
            'JSMin : throw on ' . $label
        );
    }

    public function exceptionProvider() {
        return array(
            array(
                '"Hello'
                ,'Unterminated String'
                ,'JSMin\\UnterminatedStringException'
                ,"JSMin: Unterminated String at byte 5: \"Hello"),
            array(
                "return /regexp\n}"
                ,'Unterminated RegExp'
                ,'JSMin\\UnterminatedRegExpException'
                ,"JSMin: Unterminated RegExp at byte 14: /regexp\n"),
            array(
                "return/regexp\n}"
                ,'Unterminated RegExp'
                ,'JSMin\\UnterminatedRegExpException'
                ,"JSMin: Unterminated RegExp at byte 13: /regexp\n"),
            array(
                ";return/regexp\n}"
                ,'Unterminated RegExp'
                ,'JSMin\\UnterminatedRegExpException'
                ,"JSMin: Unterminated RegExp at byte 14: /regexp\n"),
            array(
                ";return /regexp\n}"
                ,'Unterminated RegExp'
                ,'JSMin\\UnterminatedRegExpException'
                ,"JSMin: Unterminated RegExp at byte 15: /regexp\n"),
            array(
                "typeof/regexp\n}"
                ,'Unterminated RegExp'
                ,'JSMin\\UnterminatedRegExpException'
                ,"JSMin: Unterminated RegExp at byte 13: /regexp\n"),
            array(
                "/* Comment "
                ,'Unterminated Comment'
                ,'JSMin\\UnterminatedCommentException'
                ,"JSMin: Unterminated comment at byte 11: /* Comment "),
        );
    }

	/**
	 * This function loads all of the test cases from the specified group.
	 * Groups are created simply by populating the appropriate directories:
	 *
	 *    /tests/Resources/GROUPNAME/input/
	 *    /tests/Resources/GROUPNAME/output/
	 *
	 * Each test case should have two identically named files, with the raw
	 * javascript going in the test folder and the expected results to be in
	 * the output folder.
	 *
	 * @param $group string
	 * @return array
	 */
	public function getTestFiles($group)
	{
		$baseDir = __DIR__ . '/../../Resources/' . $group . '/';
		$testDir = $baseDir . 'input/';
		$expectDir = $baseDir . 'expected/';
		$actualDir = $baseDir . 'actual/';

		$returnData = array();

		$testFiles = scandir($testDir);
		foreach ($testFiles as $testFile) {
			if (substr($testFile, -3) !== '.js' || !file_exists(($expectDir . $testFile))) {
				continue;
			}

			$testInput = file_get_contents($testDir . $testFile);
			$expectedOutput = file_get_contents($expectDir . $testFile);
			$actualFile = $actualDir . $testFile;

			$returnData[] = array($testFile, $testInput, $expectedOutput, $actualFile);
		}

		return $returnData;
	}

	public function minifyProvider()
	{
		return $this->getTestFiles('minify');
	}
}
