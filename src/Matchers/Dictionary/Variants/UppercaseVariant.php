<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary\Variants;

use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Math\Binomial;
use ZxcvbnPhp\Multibyte\MultibyteReg;

final class UppercaseVariant
{
    public static function getVariations(MatchInterface $match): float
    {
        $word = $match->token();
        // Clean words of non-alpha characters to remove the reward effect to capitalize the first letter.
        $cleanedWord = preg_replace(MultibyteReg::ALPHA_INVERTED, '', $word);
        if (mb_strtolower($cleanedWord) === $cleanedWord || preg_match(MultibyteReg::ALL_LOWER_INVERTED, $cleanedWord)) {
            return 1;
        }

        // A capitalized word is the most common capitalization scheme,
        // so it only doubles the search space (uncapitalized + capitalized).
        // Allcaps and end-capitalized are common enough too, underestimate as 2x factor to be safe.
        foreach ([MultibyteReg::START_UPPER, MultibyteReg::END_UPPER, MultibyteReg::ALL_UPPER_INVERTED] as $regex) {
            if (preg_match($regex, $cleanedWord)) {
                return 2;
            }
        }

        return self::calculateVariations($cleanedWord);
    }

    /**
     * Calculate the number of ways to capitalize U+L uppercase+lowercase letters with U uppercase letters or less.
     * Or, if there's more uppercase than lower (for e.g. PASSwORD), the number of ways to lowercase U+L letters with L lowercase letters or less.
     */
    private static function calculateVariations(string $word): float
    {
        $wordArray = preg_split('//u', $word, -1, \PREG_SPLIT_NO_EMPTY);
        $uppercaseCount = \count(array_filter($wordArray, static fn (string $word) => preg_match(MultibyteReg::ONE_UPPER, $word)));
        $lowercaseCount = \count(array_filter($wordArray, static fn (string $word) => preg_match(MultibyteReg::ONE_LOWER, $word)));

        $variations = 0;
        for ($i = 1; $i <= min($uppercaseCount, $lowercaseCount); ++$i) {
            $variations += Binomial::binom($uppercaseCount + $lowercaseCount, $i);
        }

        return $variations;
    }
}
