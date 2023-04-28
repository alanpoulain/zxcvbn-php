<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary\Variants;

use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tChangeResult;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Math\Binomial;

final class L33tVariant
{
    public static function getVariations(MatchInterface $match): float
    {
        if (!$match instanceof DictionaryMatch || !$match->l33t()) {
            return 1;
        }
        $variations = 1;

        foreach (self::getUniqueChanges($match->l33tExtra()?->changes ?? []) as $change) {
            $substitution = $change->substitution;
            $letter = $change->letter;

            $tokenLower = mb_strtolower($match->token());

            $substitutedCount = mb_substr_count($tokenLower, $substitution);
            $unsubstitutedCount = mb_substr_count($tokenLower, $letter);

            if (0 === $substitutedCount || 0 === $unsubstitutedCount) {
                // For this substitution, password is either fully substituted (444) or fully unsubstituted (aaa).
                // Treat that as doubling the space (attacker needs to try fully substituted chars in addition to unsubstituted).
                $variations *= 2;
            } else {
                // This case is similar to capitalization:
                // with aa44a, U = 3, S = 2, attacker needs to try unsubstituted + one substituted + two substituted.
                $possibilities = 0;
                for ($i = 1; $i <= min($substitutedCount, $unsubstitutedCount); ++$i) {
                    $possibilities += Binomial::binom($substitutedCount + $unsubstitutedCount, $i);
                }
                $variations *= $possibilities;
            }
        }

        return $variations;
    }

    /**
     * @param L33tChangeResult[] $changes
     *
     * @return L33tChangeResult[]
     */
    private static function getUniqueChanges(array $changes): array
    {
        if (empty($changes)) {
            return [];
        }

        $uniqueChanges = [$changes[0]];

        foreach ($changes as $change) {
            foreach ($uniqueChanges as $uniqueChange) {
                if ($change->letter === $uniqueChange->letter && $change->substitution === $uniqueChange->substitution) {
                    break;
                }
                $uniqueChanges[] = $change;
            }
        }

        return $uniqueChanges;
    }
}
