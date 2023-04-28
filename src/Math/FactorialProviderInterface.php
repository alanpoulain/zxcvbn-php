<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math;

interface FactorialProviderInterface
{
    /**
     * Calculates factorial of n (n!).
     */
    public function fact(int $n): float;
}
