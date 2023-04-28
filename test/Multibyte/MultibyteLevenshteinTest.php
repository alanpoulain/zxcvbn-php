<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Multibyte;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Multibyte\MultibyteLevenshtein;

#[CoversClass(MultibyteLevenshtein::class)]
final class MultibyteLevenshteinTest extends TestCase
{
    public function testLevenshteinAscii(): void
    {
        self::assertSame(2, MultibyteLevenshtein::mbLevenshtein('doctor', 'dooator'));
    }

    public function testLevenshteinMultibyte(): void
    {
        self::assertSame(1, MultibyteLevenshtein::mbLevenshtein('doctor', 'dŏctor'));
    }
}
