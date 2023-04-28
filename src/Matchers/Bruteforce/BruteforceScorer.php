<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Bruteforce;

use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Scorer;

final class BruteforceScorer extends AbstractScorer
{
    private const BRUTEFORCE_CARDINALITY = 10.;

    protected static function getRawGuesses(MatchInterface $match, Options $options): float
    {
        $guesses = self::BRUTEFORCE_CARDINALITY ** mb_strlen($match->token());
        if (\INF === $guesses) {
            return \PHP_FLOAT_MAX;
        }

        // Small detail: make bruteforce matches at minimum one guess bigger than smallest allowed submatch guesses,
        // such that non-bruteforce submatches over the same [begin..end] take precedence.
        $minGuesses = Scorer::MIN_SUBMATCH_GUESSES_MULTI_CHAR;
        if (1 === mb_strlen($match->token())) {
            $minGuesses = Scorer::MIN_SUBMATCH_GUESSES_SINGLE_CHAR;
        }
        ++$minGuesses;

        return max($guesses, $minGuesses);
    }

    public static function getPattern(): string
    {
        return BruteforceMatch::PATTERN;
    }
}
