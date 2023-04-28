<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Sequence;

use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Multibyte\MultibyteReg;
use ZxcvbnPhp\Options;

final class SequenceMatcher implements MatcherInterface
{
    private const MAX_DELTA = 5;

    /**
     * Match sequences of three or more characters.
     *
     * @return SequenceMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        $matches = [];
        $passwordLength = mb_strlen($password);

        if ($passwordLength <= 1) {
            return [];
        }

        /*
         * Identifies sequences by looking for repeated differences in unicode codepoint.
         * This allows skipping, such as 9753, and also matches some extended unicode sequences such as Greek and Cyrillic alphabets.
         *
         * For example, consider the input 'abcdb975zy'
         *
         * password: a   b   c   d   b    9   7   5   z   y
         * index:    0   1   2   3   4    5   6   7   8   9
         * delta:      1   1   1  -2  -41  -2  -2  69   1
         *
         * Expected result:
         * [(begin, end, delta), ...] = [(0, 3, 1), (5, 7, -2), (8, 9, 1)]
         */
        $begin = 0;
        $lastDelta = null;
        for ($index = 1; $index < $passwordLength; ++$index) {
            $delta = mb_ord(mb_substr($password, $index, 1)) - mb_ord(mb_substr($password, $index - 1, 1));
            if (null === $lastDelta) {
                $lastDelta = $delta;
            }
            if ($lastDelta === $delta) {
                continue;
            }

            if ($match = self::findSequenceMatch($password, $begin, $index - 1, $lastDelta)) {
                $matches[] = $match;
            }
            $begin = $index - 1;
            $lastDelta = $delta;
        }

        if ($match = self::findSequenceMatch($password, $begin, $passwordLength - 1, $lastDelta)) {
            $matches[] = $match;
        }

        return Matcher::sortMatches($matches);
    }

    private static function findSequenceMatch(string $password, int $begin, int $end, int $delta): ?SequenceMatch
    {
        if ($end - $begin > 1 || 1 === abs($delta)) {
            if (abs($delta) > 0 && abs($delta) <= self::MAX_DELTA) {
                $token = mb_substr($password, $begin, $end - $begin + 1);
                if (preg_match(MultibyteReg::ALL_LOWER, $token)) {
                    $sequenceName = SequenceMatch::SEQUENCE_NAME_LOWER;
                    $sequenceSpace = 2155;
                } elseif (preg_match(MultibyteReg::ALL_UPPER, $token)) {
                    $sequenceName = SequenceMatch::SEQUENCE_NAME_UPPER;
                    $sequenceSpace = 1791;
                } elseif (preg_match(MultibyteReg::ALL_NUMBER, $token)) {
                    $sequenceName = SequenceMatch::SEQUENCE_NAME_DIGITS;
                    $sequenceSpace = 650 + 236 + 895;
                } else {
                    $sequenceName = SequenceMatch::SEQUENCE_NAME_UNICODE;
                    $sequenceSpace = 143_924 - 2155 - 1791 - (650 + 236 + 895);
                }

                return new SequenceMatch(
                    password: $password,
                    begin: $begin,
                    end: $end,
                    token: $token,
                    sequenceName: $sequenceName,
                    sequenceSpace: $sequenceSpace,
                    ascending: $delta > 0
                );
            }
        }

        return null;
    }

    public static function getPattern(): string
    {
        return SequenceMatch::PATTERN;
    }
}
