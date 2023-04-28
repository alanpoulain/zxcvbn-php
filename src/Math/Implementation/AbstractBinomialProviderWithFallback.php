<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math\Implementation;

use ZxcvbnPhp\Math\BinomialProviderInterface;

abstract class AbstractBinomialProviderWithFallback extends AbstractBinomialProvider
{
    private ?BinomialProviderInterface $fallback = null;

    protected function calculate(int $n, int $k): float
    {
        return $this->tryCalculate($n, $k) ?? $this->getFallbackProvider()->calculate($n, $k);
    }

    abstract protected function tryCalculate(int $n, int $k): ?float;

    abstract protected function initFallbackProvider(): BinomialProviderInterface;

    protected function getFallbackProvider(): BinomialProviderInterface
    {
        if (null === $this->fallback) {
            $this->fallback = $this->initFallbackProvider();
        }

        return $this->fallback;
    }
}
