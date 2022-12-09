# Changelog

## 2.1

* Added new expect-toBe and expect-notTo BDD Syntax.
* Added full documentation about Expectations.
* Fixed minor bugs.
* Deleted RoboFile and VERSION file.
* **BC:** `expect` function now works with expectations instead of verifiers.

## 2.0

* Support for Chained Verifiers.
* The Verify API is now fully based on the PHPUnit public API.
* Improved IDE autocompletion depending on the type of data you want to verify
* Simplified data validations.
* Improved code quality, performance and maintainability.
* See **BC** details in the UPGRADE.md file.

## 1.5

* Support for full PHPUnit API `(42 new verifiers!)`
* Updated `supported_verifiers.md` documentation.

## 1.4

* Improved code quality and maintainability.
* Used strict types and namespaces.
Created exception `InvalidVerifyException.php` in case verify is used with some invalid data.
* Added documentation for all verifiers.
* Divided the verifiers into traits depending on the type of data they verify.
* Added data validations with php issers functions and instanceof.

* **BC:** `equalXMLStructure` and its corresponding test were removed.
* **BC:** hasntKey verifier renamed to hasNotKey for clarity.
* **BC:** Removed support for PHP 7.0 and its corresponding versions of `PHPUnit` and `phpunit-wrapper`.