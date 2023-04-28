<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Repeat;

use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Multibyte\MultibyteReg;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Scorer;

final class RepeatMatcher implements MatcherInterface
{
    private const GREEDY = '/(.+)\1+/u';
    private const LAZY = '/(.+?)\1+/u';
    private const ANCHORED_LAZY = '/^(.+?)\1+$/u';

    /**
     * Match 3 or more repeated characters.
     *
     * @return RepeatMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        $scorer = new Scorer($options);
        $matcher = new Matcher($options);

        /** @var RepeatMatch[] $matches */
        $matches = [];
        $lastIndex = 0;
        while ($lastIndex < mb_strlen($password)) {
            $greedyMatches = MultibyteReg::mbRegMatchAll($password, self::GREEDY, $lastIndex);
            $lazyMatches = MultibyteReg::mbRegMatchAll($password, self::LAZY, $lastIndex);

            if (empty($greedyMatches)) {
                break;
            }

            if (mb_strlen($greedyMatches[0][0]->token()) > mb_strlen($lazyMatches[0][0]->token())) {
                // Greedy beats lazy for 'aabaab'.
                // greedy: [aabaab, aab]
                // lazy:   [aa,     a]
                $match = $greedyMatches[0];
                // Greedy's repeated string might itself be repeated, e.g. 'aabaab' in 'aabaabaabaab'.
                // Run an anchored lazy match on greedy's repeated string to find the shortest repeated string.
                preg_match(self::ANCHORED_LAZY, $match[0]->token(), $anchoredMatch);
                $repeatedChar = $anchoredMatch[1];
            } else {
                // Lazy beats greedy for 'aaaaa'.
                // greedy: [aaaa,  aa]
                // lazy:   [aaaaa, a]
                $match = $lazyMatches[0];
                $repeatedChar = $match[1]->token();
            }

            $baseAnalysis = $scorer->getMostGuessableMatchSequence($repeatedChar, $matcher->getMatches($repeatedChar));
            $baseMatches = $baseAnalysis->sequence;
            $baseGuesses = $baseAnalysis->guesses;

            $repeatCount = mb_strlen($match[0]->token()) / mb_strlen($repeatedChar);

            $matches[] = new RepeatMatch(
                password: $password,
                begin: $match[0]->begin(),
                end: $match[0]->end(),
                token: $match[0]->token(),
                baseMatches: $baseMatches,
                baseGuesses: $baseGuesses,
                repeatCount: $repeatCount,
                repeatedChar: $repeatedChar,
            );

            $lastIndex = $match[0]->end() + 1;
        }

        return Matcher::sortMatches($matches);
    }

    public static function getPattern(): string
    {
        return RepeatMatch::PATTERN;
    }
}
