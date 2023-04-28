<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary;

use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Multibyte\MultibyteString;
use ZxcvbnPhp\Options;

/**
 * Match occurrences of reversed dictionary words in password.
 */
final class ReverseDictionaryMatcher implements MatcherInterface
{
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        $reverseMatches = [];
        /** @var DictionaryMatch[] $matches */
        $matches = DictionaryMatcher::match(MultibyteString::mbStrRev($password), $options, $userInputs);
        foreach ($matches as $match) {
            $reverseMatches[] = new DictionaryMatch(
                password: MultibyteString::mbStrRev($match->password()),
                begin: mb_strlen($password) - 1 - $match->end(),
                end: mb_strlen($password) - 1 - $match->begin(),
                token: MultibyteString::mbStrRev($match->token()),
                matchedWord: $match->matchedWord(),
                rank: $match->rank(),
                dictionaryName: $match->dictionaryName(),
                reversed: true,
                l33t: $match->l33t(),
                levenshteinDistance: $match->levenshteinDistance(),
            );
        }

        return Matcher::sortMatches($reverseMatches);
    }

    public static function getPattern(): string
    {
        return DictionaryMatch::PATTERN;
    }
}
