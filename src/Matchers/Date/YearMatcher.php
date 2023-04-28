<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Date;

use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Multibyte\MultibyteReg;
use ZxcvbnPhp\Options;

final class YearMatcher implements MatcherInterface
{
    private const RECENT_YEAR = '/19\d\d|200\d|201\d|202\d/u';

    /**
     * Match occurrences of years in a password.
     *
     * @return DateMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        $matches = [];
        $groups = MultibyteReg::mbRegMatchAll($password, self::RECENT_YEAR);
        foreach ($groups as $captures) {
            $matches[] = new DateMatch(
                password: $password,
                begin: $captures[0]->begin(),
                end: $captures[0]->end(),
                token: $captures[0]->token(),
                day: -1,
                month: -1,
                year: (int) $captures[0]->token(),
                separator: ''
            );
        }

        return Matcher::sortMatches($matches);
    }

    public static function getPattern(): string
    {
        return DateMatch::PATTERN;
    }
}
