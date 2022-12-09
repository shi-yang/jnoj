<?php

declare(strict_types=1);

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Module;
use Codeception\PHPUnit\TestCase;
use Codeception\TestInterface;
use Codeception\Util\FileSystem as Util;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\Finder\Finder;

/**
 * Module for testing local filesystem.
 * Fork it to extend the module for FTP, Amazon S3, others.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * Module was developed to test Codeception itself.
 */
class Filesystem extends Module
{
    protected string $file = '';

    protected string $filePath = '';

    protected string $path = '';

    public function _before(TestInterface $test)
    {
        $this->path = Configuration::projectDir();
    }

    /**
     * Enters a directory In local filesystem.
     * Project root directory is used by default
     */
    public function amInPath(string $path): void
    {
        chdir($this->path = $this->absolutizePath($path) . DIRECTORY_SEPARATOR);
        $this->debug('Moved to ' . getcwd());
    }

    protected function absolutizePath(string $path): string
    {
        // *nix way
        if (strpos($path, '/') === 0) {
            return $path;
        }

        // windows
        if (strpos($path, ':\\') === 1) {
            return $path;
        }

        return $this->path . $path;
    }

    /**
     * Opens a file and stores it's content.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ```
     */
    public function openFile(string $filename): void
    {
        $this->file = file_get_contents($this->absolutizePath($filename));
        $this->filePath = $filename;
    }

    /**
     * Deletes a file
     *
     * ``` php
     * <?php
     * $I->deleteFile('composer.lock');
     * ```
     */
    public function deleteFile(string $filename): void
    {
        if (!file_exists($this->absolutizePath($filename))) {
            TestCase::fail('file not found');
        }

        unlink($this->absolutizePath($filename));
    }

    /**
     * Deletes directory with all subdirectories
     *
     * ``` php
     * <?php
     * $I->deleteDir('vendor');
     * ```
     */
    public function deleteDir(string $dirname): void
    {
        $dir = $this->absolutizePath($dirname);
        Util::deleteDir($dir);
    }

    /**
     * Copies directory with all contents
     *
     * ``` php
     * <?php
     * $I->copyDir('vendor','old_vendor');
     * ```
     */
    public function copyDir(string $src, string $dst): void
    {
        Util::copyDir($src, $dst);
    }

    /**
     * Checks If opened file has `text` in it.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ```
     */
    public function seeInThisFile(string $text): void
    {
        $this->assertStringContainsString($text, $this->file, "No text '{$text}' in currently opened file");
    }

    /**
     * Checks If opened file has the `number` of new lines.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeNumberNewLines(5);
     * ```
     *
     * @param int $number New lines
     */
    public function seeNumberNewLines(int $number): void
    {
        $lines = preg_split('#[\n\r]#', $this->file);

        $this->assertTrue(
            $number === count($lines),
            "The number of new lines does not match with {$number}"
        );
    }

    /**
     * Checks that contents of currently opened file matches $regex
     */
    public function seeThisFileMatches(string $regex): void
    {
        $this->assertRegExp($regex, $this->file, "Contents of currently opened file does not match '{$regex}'");
    }

    /**
     * Checks the strict matching of file contents.
     * Unlike `seeInThisFile` will fail if file has something more than expected lines.
     * Better to use with HEREDOC strings.
     * Matching is done after removing "\r" chars from file content.
     *
     * ``` php
     * <?php
     * $I->openFile('process.pid');
     * $I->seeFileContentsEqual('3192');
     * ```
     */
    public function seeFileContentsEqual(string $text): void
    {
        $file = str_replace("\r", '', $this->file);
        TestCase::assertEquals($text, $file);
    }

    /**
     * Checks If opened file doesn't contain `text` in it
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->dontSeeInThisFile('codeception/codeception');
     * ```
     */
    public function dontSeeInThisFile(string $text): void
    {
        $this->assertStringNotContainsString($text, $this->file, "Found text '{$text}' in currently opened file");
    }

    /**
     * Deletes a file
     */
    public function deleteThisFile(): void
    {
        $this->deleteFile($this->filePath);
    }

    /**
     * Checks if file exists in path.
     * Opens a file when it's exists
     *
     * ``` php
     * <?php
     * $I->seeFileFound('UserModel.php','app/models');
     * ```
     */
    public function seeFileFound(string $filename, string $path = ''): void
    {
        if ($path === '' && file_exists($filename)) {
            $this->openFile($filename);
            TestCase::assertFileExists($filename);
            return;
        }

        $found = $this->findFileInPath($filename, $path);

        if ($found === false) {
            $this->fail(sprintf('File "%s" not found at "%s"', $filename, $path));
        }

        $this->openFile($found);
        TestCase::assertFileExists($found);
    }

    /**
     * Checks if file does not exist in path
     */
    public function dontSeeFileFound(string $filename, string $path = ''): void
    {
        if ($path === '') {
            TestCase::assertFileDoesNotExist($filename);
            return;
        }

        $found = $this->findFileInPath($filename, $path);

        if ($found === false) {
            //this line keeps a count of assertions correct
            TestCase::assertTrue(true);
            return;
        }

        TestCase::assertFileDoesNotExist($found);
    }

    /**
     * Finds the first matching file
     *
     * @throws AssertionFailedError When path does not exist
     * @return string|false Path to the first matching file
     */
    private function findFileInPath(string $filename, string $path)
    {
        $path = $this->absolutizePath($path);
        if (!file_exists($path)) {
            $this->fail(sprintf('Directory does not exist: %s', $path));
        }

        $files = Finder::create()->files()->name($filename)->in($path);
        if ($files->count() === 0) {
            return false;
        }

        foreach ($files as $file) {
            return $file->getRealPath();
        }
    }


    /**
     * Erases directory contents
     *
     * ``` php
     * <?php
     * $I->cleanDir('logs');
     * ```
     */
    public function cleanDir(string $dirname): void
    {
        $path = $this->absolutizePath($dirname);
        Util::doEmptyDir($path);
    }

    /**
     * Saves contents to file
     */
    public function writeToFile(string $filename, string $contents): void
    {
        file_put_contents($filename, $contents);
    }
}
