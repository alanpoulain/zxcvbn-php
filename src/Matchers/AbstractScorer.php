<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers;

use ZxcvbnPhp\Options;
use ZxcvbnPhp\Scorer;

abstract class AbstractScorer implements ScorerInterface
{
    abstract protected static function getRawGuesses(MatchInterface $match, Options $options): float;

    public static function getGuesses(MatchInterface $match, Options $options): float
    {
        return max(static::getRawGuesses($match, $options), static::getMinimumGuesses($match));
    }

    protected static function getMinimumGuesses(MatchInterface $match): float
    {
        if (mb_strlen($match->token()) < mb_strlen($match->password())) {
            if (1 === mb_strlen($match->token())) {
                return Scorer::MIN_SUBMATCH_GUESSES_SINGLE_CHAR;
            }

            return Scorer::MIN_SUBMATCH_GUESSES_MULTI_CHAR;
        }

        return 0;
    }
}
