<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math\Implementation;

use ZxcvbnPhp\Math\FactorialProviderInterface;

final class FactorialProviderInt implements FactorialProviderInterface
{
    /**
     * Unoptimized.
     * To call only on small n.
     */
    public function fact(int $n): float
    {
        if ($n < 2) {
            return 1;
        }
        $f = 1;
        for ($i = 2; $i <= $n; ++$i) {
            $f *= $i;
        }

        return (float) $f;
    }
}
