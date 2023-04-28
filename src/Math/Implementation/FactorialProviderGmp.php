<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math\Implementation;

use ZxcvbnPhp\Math\FactorialProviderInterface;

final class FactorialProviderGmp implements FactorialProviderInterface
{
    public function fact(int $n): float
    {
        return (float) gmp_strval(gmp_fact($n));
    }
}
