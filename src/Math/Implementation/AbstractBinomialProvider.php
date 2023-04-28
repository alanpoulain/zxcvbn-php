<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math\Implementation;

use ZxcvbnPhp\Math\BinomialProviderInterface;

abstract class AbstractBinomialProvider implements BinomialProviderInterface
{
    public function binom(int $n, int $k): float
    {
        if ($k < 0 || $n < 0) {
            throw new \DomainException('n and k must be non-negative');
        }

        if ($k > $n) {
            return 0;
        }

        if (0 === $k) {
            return 1;
        }

        // $k and $n - $k will always produce the same value, so use smaller of the two.
        $k = min($k, $n - $k);

        return $this->calculate($n, $k);
    }

    abstract protected function calculate(int $n, int $k): float;
}
