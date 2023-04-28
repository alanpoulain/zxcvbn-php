<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers;

use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Matchers\ScorerInterface;
use ZxcvbnPhp\Options;

final class MockScorer implements ScorerInterface
{
    public static function getGuesses(MatchInterface $match, Options $options): float
    {
        if (!is_a($match, MockMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', MockMatch::class));
        }

        return $match->guesses;
    }

    public static function getPattern(): string
    {
        return 'mock';
    }
}
