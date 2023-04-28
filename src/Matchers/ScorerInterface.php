<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers;

use ZxcvbnPhp\Options;

interface ScorerInterface
{
    public static function getGuesses(MatchInterface $match, Options $options): float;

    public static function getPattern(): string;
}
