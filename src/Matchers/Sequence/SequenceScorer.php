<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Sequence;

use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;

final class SequenceScorer extends AbstractScorer
{
    protected static function getRawGuesses(MatchInterface $match, Options $options): float
    {
        if (!is_a($match, SequenceMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', SequenceMatch::class));
        }

        $firstCharacter = mb_substr($match->token(), 0, 1);
        $guesses = 0;

        if (\in_array($firstCharacter, ['a', 'A', 'z', 'Z', '0', '1', '9'], true)) {
            // Lower guesses for obvious starting points.
            $guesses += 4;
        } elseif (ctype_digit($firstCharacter)) {
            // Digits.
            $guesses += 10;
        } else {
            // Could give a higher base for uppercase, assigning 26 to both upper and lower sequences is more conservative.
            $guesses += 26;
        }

        if (!$match->ascending()) {
            // Need to try a descending sequence in addition to every ascending sequence -> 2x guesses.
            $guesses *= 2;
        }

        return $guesses * mb_strlen($match->token());
    }

    public static function getPattern(): string
    {
        return SequenceMatch::PATTERN;
    }
}
