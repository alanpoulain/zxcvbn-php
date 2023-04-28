<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Math;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Math\Factorial;
use ZxcvbnPhp\Math\FactorialProviderInterface;
use ZxcvbnPhp\Math\Implementation\FactorialProviderGmp;
use ZxcvbnPhp\Math\Implementation\FactorialProviderInt;

#[CoversClass(Factorial::class)]
#[CoversClass(FactorialProviderGmp::class)]
#[CoversClass(FactorialProviderInt::class)]
final class FactorialTest extends TestCase
{
    public function testHasProvider(): void
    {
        self::assertNotEmpty(Factorial::getUsableProviderClasses());
    }

    public function testFact(): void
    {
        self::assertSame(1307674368000.0, Factorial::fact(15));
    }

    public static function provideFactorialCases(): iterable
    {
        return [
            [0,                   1.0],
            [1,                   1.0],
            [2,                   2.0],
            [18,   6402373705728000.0],
            [33, 8.683317618811886e36],
        ];
    }

    #[DataProvider('provideFactorialCases')]
    public function testFactorial(int $n, float $expected): void
    {
        foreach (Factorial::getUsableProviderClasses() as $providerClass) {
            $provider = new $providerClass();
            self::assertInstanceOf(FactorialProviderInterface::class, $provider);

            $value = $provider->fact($n);
            self::assertSame($expected, $value, "{$providerClass} returns expected result for {$n}!");
        }
    }
}
