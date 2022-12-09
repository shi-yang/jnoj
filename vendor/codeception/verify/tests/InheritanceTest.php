<?php

declare(strict_types=1);

include_once __DIR__.'/../src/Codeception/bootstrap.php';

use Codeception\Verify\Verify;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

include __DIR__.'/../vendor/autoload.php';

final class InheritanceTest extends TestCase
{
    public function testVerifyCanBeExtended(): void
    {
        $myVerify = new MyVerify;

        $myVerify->success();

        $myVerify::Mixed('this also')->notEquals('works');

        verify(new MyVerify())->instanceOf(Verify::class);
    }
}


final class MyVerify extends Verify
{
    public function __construct($actual = null)
    {
        parent::__construct($actual);
    }

    public function success(string $message = ''): void
    {
        Assert::assertTrue(true, $message);
    }
}