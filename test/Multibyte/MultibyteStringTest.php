<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Multibyte;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Multibyte\MultibyteString;

#[CoversClass(MultibyteString::class)]
final class MultibyteStringTest extends TestCase
{
    public function testMbStrRev(): void
    {
        self::assertSame('はちにんこ', MultibyteString::mbStrRev('こんにちは'));
    }
}
