<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Multibyte;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Multibyte\MbRegMatch;
use ZxcvbnPhp\Multibyte\MultibyteReg;

#[CoversClass(MultibyteReg::class)]
#[CoversClass(MbRegMatch::class)]
final class MultibyteRegTest extends TestCase
{
    public function testSimpleMbRegMatchAll(): void
    {
        $matches = MultibyteReg::mbRegMatchAll('fishfishfish', '/fish/');
        $expectedMatches = [
            [
                new MbRegMatch(begin: 0, end: 3, token: 'fish'),
            ],
            [
                new MbRegMatch(begin: 4, end: 7, token: 'fish'),
            ],
            [
                new MbRegMatch(begin: 8, end: 11, token: 'fish'),
            ],
        ];

        self::assertEquals($expectedMatches, $matches);
    }

    public function testCaptureGroupsMbRegMatchAll(): void
    {
        $matches = MultibyteReg::mbRegMatchAll('abc123def456', '/([a-z]+)(\d+)/');
        $expectedMatches = [
            [
                new MbRegMatch(begin: 0, end: 5, token: 'abc123'),
                new MbRegMatch(begin: 0, end: 2, token: 'abc'),
                new MbRegMatch(begin: 3, end: 5, token: '123'),
            ],
            [
                new MbRegMatch(begin: 6, end: 11, token: 'def456'),
                new MbRegMatch(begin: 6, end: 8, token: 'def'),
                new MbRegMatch(begin: 9, end: 11, token: '456'),
            ],
        ];

        self::assertEquals($expectedMatches, $matches);
    }

    public function testOffsetMbRegMatchAll(): void
    {
        $matches = MultibyteReg::mbRegMatchAll('こんにちはabcdef456', '/([a-z]+)(\d+)/', 8);
        $expectedMatches = [
            [
                new MbRegMatch(begin: 8, end: 13, token: 'def456'),
                new MbRegMatch(begin: 8, end: 10, token: 'def'),
                new MbRegMatch(begin: 11, end: 13, token: '456'),
            ],
        ];

        self::assertEquals($expectedMatches, $matches);
    }

    public function testNoMatchMbRegMatchAll(): void
    {
        $matches = MultibyteReg::mbRegMatchAll('123456', '/([a-z]+)(\d+)/');

        self::assertEmpty($matches);
    }
}
