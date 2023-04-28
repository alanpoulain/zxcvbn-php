<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Separator;

use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;

final class SeparatorScorer extends AbstractScorer
{
    protected static function getRawGuesses(MatchInterface $match, Options $options): float
    {
        return \count(SeparatorMatcher::$separators);
    }

    public static function getPattern(): string
    {
        return SeparatorMatch::PATTERN;
    }
}
