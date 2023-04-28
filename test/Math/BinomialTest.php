<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Math;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Math\Binomial;
use ZxcvbnPhp\Math\BinomialProviderInterface;
use ZxcvbnPhp\Math\Implementation\AbstractBinomialProvider;
use ZxcvbnPhp\Math\Implementation\AbstractBinomialProviderWithFallback;
use ZxcvbnPhp\Math\Implementation\BinomialProviderFloat64;
use ZxcvbnPhp\Math\Implementation\BinomialProviderGmp;
use ZxcvbnPhp\Math\Implementation\BinomialProviderInt64;

#[CoversClass(Binomial::class)]
#[CoversClass(AbstractBinomialProvider::class)]
#[CoversClass(AbstractBinomialProviderWithFallback::class)]
#[CoversClass(BinomialProviderInt64::class)]
#[CoversClass(BinomialProviderGmp::class)]
#[CoversClass(BinomialProviderFloat64::class)]
final class BinomialTest extends TestCase
{
    public function testHasProvider(): void
    {
        self::assertNotEmpty(Binomial::getUsableProviderClasses());
    }

    public function testBinom(): void
    {
        self::assertSame(15.0, Binomial::binom(6, 4));
    }

    public static function provideBinomialCoefficientCases(): iterable
    {
        return [
            [0,     0,            1.0],
            [1,     0,            1.0],
            [5,     0,            1.0],
            [0,     1,            0.0],
            [0,     5,            0.0],
            [2,     1,            2.0],
            [4,     2,            6.0],
            [33,    7,      4272048.0],
            [206,   202,   72867865.0],
            [3,     5,            0.0],
            [29847, 2,    445406781.0],
            [49,    12, 92263734836.0],
            [\PHP_INT_MAX, 4, 3.015418990555109e74],
        ];
    }

    #[DataProvider('provideBinomialCoefficientCases')]
    public function testBinomialCoefficient(float|int $n, int $k, float $expected): void
    {
        foreach (Binomial::getUsableProviderClasses() as $providerClass) {
            $provider = new $providerClass();
            self::assertInstanceOf(BinomialProviderInterface::class, $provider);

            $value = $provider->binom($n, $k);
            self::assertSame($expected, $value, "{$providerClass} returns expected result for ({$n}, {$k})");

            if ($k <= $n) {  // Behavior is undefined for $k > n; don't test that
                $flippedValue = $provider->binom($n, $n - $k);
                self::assertSame($value, $flippedValue, "{$providerClass} is symmetrical");
            }
        }
    }

    public static function provideBinomialNegativeCases(): iterable
    {
        return [
            [-6, 4],
            [6, -4],
        ];
    }

    #[DataProvider('provideBinomialNegativeCases')]
    public function testBinomialNegative(int $n, int $k): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('n and k must be non-negative');

        Binomial::binom($n, $k);
    }
}
