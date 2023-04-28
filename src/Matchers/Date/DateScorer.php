<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Date;

use ZxcvbnPhp\Date\Now;
use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;

final class DateScorer extends AbstractScorer
{
    private const MIN_YEAR_SPACE = 20;

    protected static function getRawGuesses(MatchInterface $match, Options $options): float
    {
        if (!is_a($match, DateMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', DateMatch::class));
        }

        // Base guesses: (year distance from reference year) * num days.
        $guesses = max(abs($match->year() - Now::getYear()), self::MIN_YEAR_SPACE);

        // Only years.
        if (-1 === $match->month()) {
            return $guesses;
        }

        $guesses *= 365;

        // Add factor of 4 for separator selection (one of ~4 choices).
        if ($match->separator()) {
            $guesses *= 4;
        }

        return $guesses;
    }

    public static function getPattern(): string
    {
        return DateMatch::PATTERN;
    }
}
