<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math\Implementation;

final class BinomialProviderGmp extends AbstractBinomialProvider
{
    protected function calculate(int $n, int $k): float
    {
        return (float) gmp_strval(gmp_binomial($n, $k));
    }
}
