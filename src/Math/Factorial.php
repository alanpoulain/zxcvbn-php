<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math;

use ZxcvbnPhp\Math\Implementation\FactorialProviderGmp;
use ZxcvbnPhp\Math\Implementation\FactorialProviderInt;

/**
 * @extends AbstractProviderInstance<FactorialProviderInterface>
 */
final class Factorial extends AbstractProviderInstance
{
    /**
     * Calculates factorial of n (n!).
     */
    public static function fact(int $n): float
    {
        return self::getProvider()->fact($n);
    }

    protected static function getPossibleProviderClasses(): array
    {
        return [
            FactorialProviderGmp::class => \function_exists('gmp_fact'),
            FactorialProviderInt::class => true,
        ];
    }
}
