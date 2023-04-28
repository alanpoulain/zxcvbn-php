<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Bruteforce;

use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Options;

final class BruteforceMatcher implements MatcherInterface
{
    /**
     * @return BruteforceMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        // Matches entire string.
        return [
            new BruteforceMatch(
                password: $password,
                begin: 0,
                end: mb_strlen($password) - 1,
                token: $password,
            ),
        ];
    }

    public static function getPattern(): string
    {
        return BruteforceMatch::PATTERN;
    }
}
