<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary;

use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Multibyte\MultibyteLevenshtein;
use ZxcvbnPhp\Options;

final class DictionaryMatcher implements MatcherInterface
{
    /**
     * Match occurrences of dictionary words in password.
     *
     * @return DictionaryMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = [], bool $useLevenshtein = true): array
    {
        [$rankedDictionaries, $rankedDictionariesMaxWordSize] = Configurator::getRankedDictionariesWithUserInputs(
            $options->rankedDictionaries,
            $options->rankedDictionariesMaxWordSize,
            $userInputs
        );

        $matches = [];
        foreach ($rankedDictionaries as $dictionaryName => $rankedDictionary) {
            $matches = [...$matches, ...self::dictionaryMatch($password, $dictionaryName, $rankedDictionary, $rankedDictionariesMaxWordSize[$dictionaryName], $options, $useLevenshtein)];
        }

        return Matcher::sortMatches($matches);
    }

    public static function getPattern(): string
    {
        return DictionaryMatch::PATTERN;
    }

    /**
     * Attempts to find the provided password (as well as all possible substrings) in a dictionary.
     */
    private static function dictionaryMatch(string $password, string $dictionaryName, array $rankedDictionary, int $maxWordSize, Options $options, bool $useLevenshtein): array
    {
        $matches = [];

        $passwordLength = mb_strlen($password);
        $passwordLower = mb_strtolower($password);

        $useLevenshteinDistance = $options->useLevenshteinDistance && $useLevenshtein;
        $searchWidth = $useLevenshteinDistance ? $passwordLength : min($maxWordSize, $passwordLength);
        for ($begin = 0; $begin < $passwordLength; ++$begin) {
            $searchEnd = min($begin + $searchWidth, $passwordLength);
            for ($end = $begin; $end < $searchEnd; ++$end) {
                $word = mb_substr($passwordLower, $begin, $end - $begin + 1);
                $isInDictionary = isset($rankedDictionary[$word]);

                $levenshteinDistance = -1;
                $levenshteinWord = '';
                // Only use levenshtein distance on full password to minimize the performance drop and to avoid false positives.
                $isFullPassword = 0 === $begin && $end === $passwordLength - 1;
                if (
                    $useLevenshteinDistance
                    && $isFullPassword
                    && !$isInDictionary
                ) {
                    [$levenshteinDistance, $levenshteinWord] = self::findLevenshteinDistance($word, $rankedDictionary, $options->levenshteinThreshold);
                }

                if ($isInDictionary || -1 !== $levenshteinDistance) {
                    $actualWord = -1 !== $levenshteinDistance ? $levenshteinWord : $word;

                    $matches[] = new DictionaryMatch(
                        password: $password,
                        begin: $begin,
                        end: $end,
                        token: mb_substr($password, $begin, $end - $begin + 1),
                        matchedWord: $actualWord,
                        rank: $rankedDictionary[$actualWord],
                        dictionaryName: $dictionaryName,
                        reversed: false,
                        l33t: false,
                        levenshteinDistance: $levenshteinDistance,
                    );
                }
            }
        }

        return $matches;
    }

    /**
     * @return array{0: int, 1: string}
     */
    private static function findLevenshteinDistance(string $password, array $rankedDictionary, int $threshold): array
    {
        $passwordLength = mb_strlen($password);

        foreach (array_keys($rankedDictionary) as $word) {
            $wordLength = mb_strlen((string) $word);
            $actualThreshold = self::getActualThreshold($passwordLength, $wordLength, $threshold);
            if (abs($passwordLength - $wordLength) > $actualThreshold) {
                continue;
            }
            $levenshteinDistance = MultibyteLevenshtein::mbLevenshtein($password, (string) $word);
            if ($levenshteinDistance <= $actualThreshold) {
                return [$levenshteinDistance, $word];
            }
        }

        return [-1, ''];
    }

    private static function getActualThreshold(int $passwordLength, int $wordLength, int $threshold): int
    {
        $isPasswordTooShort = $passwordLength <= $wordLength;
        $isThresholdLongerThanPassword = $passwordLength <= $threshold;

        // If password is too small, use password length divided by 4 while threshold needs to be at least 1.
        return $isPasswordTooShort || $isThresholdLongerThanPassword ? (int) ceil($passwordLength / 4) : $threshold;
    }
}
