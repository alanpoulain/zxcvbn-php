<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math;

interface BinomialProviderInterface
{
    /**
     * Calculate binomial coefficient (n choose k).
     */
    public function binom(int $n, int $k): float;
}
