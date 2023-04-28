<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Repeat;

use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;

final class RepeatScorer extends AbstractScorer
{
    protected static function getRawGuesses(MatchInterface $match, Options $options): float
    {
        if (!is_a($match, RepeatMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', RepeatMatch::class));
        }

        return $match->baseGuesses() * $match->repeatCount();
    }

    public static function getPattern(): string
    {
        return RepeatMatch::PATTERN;
    }
}
