<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math;

use ZxcvbnPhp\Math\Implementation\BinomialProviderFloat64;
use ZxcvbnPhp\Math\Implementation\BinomialProviderGmp;
use ZxcvbnPhp\Math\Implementation\BinomialProviderInt64;

/**
 * @extends AbstractProviderInstance<BinomialProviderInterface>
 */
final class Binomial extends AbstractProviderInstance
{
    /**
     * Calculate binomial coefficient (n choose k).
     */
    public static function binom(int $n, int $k): float
    {
        return self::getProvider()->binom($n, $k);
    }

    protected static function getPossibleProviderClasses(): array
    {
        return [
            BinomialProviderGmp::class => \function_exists('gmp_binomial'),
            BinomialProviderInt64::class => \PHP_INT_SIZE >= 8,
            BinomialProviderFloat64::class => \PHP_FLOAT_DIG >= 15,
        ];
    }
}
