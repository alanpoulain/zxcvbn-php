<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

final class TopTokensCounter
{
    /**
     * Before sorting, discard all words with less than this count.
     */
    private const PRE_SORT_CUTOFF = 500;

    private const SOME_NON_ALPHA = '/[\W\d]/u';
    private const ALL_NON_ALPHA = '/^[\W\d]*$/u';

    /**
     * @var array<string, int>
     */
    private array $count = [];

    /**
     * @var array<string, 1>
     */
    private array $legomena = [];

    /**
     * @param string[] $tokens
     */
    public function addTokens(array $tokens): void
    {
        foreach ($tokens as $token) {
            $this->addToken($token);
        }
    }

    public function prune(): void
    {
        foreach (array_keys($this->legomena) as $token) {
            if (isset($this->count[$token])) {
                unset($this->count[$token]);
            }
        }
        $this->legomena = [];
    }

    public function preSortPrune(): void
    {
        foreach ($this->count as $token => $count) {
            if ($count < self::PRE_SORT_CUTOFF) {
                unset($this->count[$token]);
            }
        }
        $this->legomena = [];
    }

    public function getSortedCount(): array
    {
        arsort($this->count);

        return $this->count;
    }

    private function addToken(string $token): void
    {
        if (!$this->shouldInclude($token)) {
            return;
        }

        $normalizedToken = $this->normalize($token);

        if (isset($this->count[$normalizedToken])) {
            unset($this->legomena[$normalizedToken]);
            ++$this->count[$normalizedToken];
        } else {
            $this->legomena[$normalizedToken] = 1;
            $this->count[$normalizedToken] = 1;
        }
    }

    private function shouldInclude(string $token): bool
    {
        $isTooShort = mb_strlen($token) < 2;
        $isTooShortWithSomeSpecialChar = mb_strlen($token) <= 2 && 1 === preg_match(self::SOME_NON_ALPHA, $token);
        $isAllSpecialChars = 1 === preg_match(self::ALL_NON_ALPHA, $token);

        return !(
            $isTooShort
            || $isTooShortWithSomeSpecialChar
            || $isAllSpecialChars
        );
    }

    private function normalize(string $token): string
    {
        return mb_strtolower($token);
    }
}
