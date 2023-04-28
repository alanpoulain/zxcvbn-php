<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Date;

use ZxcvbnPhp\Date\Now;
use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Multibyte\MultibyteNumber;
use ZxcvbnPhp\Options;

final class DateMatcher implements MatcherInterface
{
    private const MIN_YEAR = 1000;
    private const MAX_YEAR = 2050;

    private const DATE_NO_SEPARATOR = '/^\d+$/u';
    /**
     * (\d{1,4})        // day, month, year
     * ([\s\/\\\\_.-])  // separator
     * (\d{1,2})        // day, month
     * \2               // same separator
     * (\d{1,4})        // day, month, year.
     */
    private const DATE_WITH_SEPARATOR = '/^(\d{1,4})([\s\/\\\\_.-])(\d{1,2})\2(\d{1,4})$/u';

    private static array $DATE_SPLITS = [
        4 => [         // For length-4 strings, e.g. 1191 or 9111, two ways to split:
            [1, 2],    // 1 1 91 (2nd split starts at index 1, 3rd at index 2)
            [2, 3],    // 91 1 1
        ],
        5 => [
            [1, 3],    // 1 11 91
            [2, 3],    // 11 1 91
        ],
        6 => [
            [1, 2],    // 1 1 1991
            [2, 4],    // 11 11 91
            [4, 5],    // 1991 1 1
        ],
        7 => [
            [1, 3],    // 1 11 1991
            [2, 3],    // 11 1 1991
            [4, 5],    // 1991 1 11
            [4, 6],    // 1991 11 1
        ],
        8 => [
            [2, 4],    // 11 11 1991
            [4, 6],    // 1991 11 11
        ],
    ];

    /**
     * Match occurrences of dates in a password.
     *
     * @return DateMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        // A "date" is recognized as:
        // - any 3-tuple that starts or ends with a 2- or 4-digit year,
        // - with 2 or 0 separator chars (1.1.91 or 1191),
        // - maybe zero-padded (01-01-91 vs 1-1-91),
        // - a month between 1 and 12,
        // - a day between 1 and 31.
        //
        // Note: this isn't true date parsing in that "feb 31st" is allowed, this doesn't check for leap years, etc.
        //
        // Recipe:
        // Start with regex to find maybe-dates, then attempt to map the integers onto month-day-year to filter the maybe-dates into dates.
        // Finally, remove matches that are substrings of other matches to reduce noise.
        //
        // Note: instead of using a lazy or greedy regex to find many dates over the full string,
        // this uses a ^...$ regex against every substring of the password -- less performant but leads to every possible date match.
        $matches = self::removeRedundantMatches(array_merge(
            self::datesWithoutSeparators($password),
            self::datesWithSeparators($password)
        ));

        return Matcher::sortMatches($matches);
    }

    public static function getPattern(): string
    {
        return DateMatch::PATTERN;
    }

    /**
     * Find dates without separators in a password.
     *
     * @return DateMatch[]
     */
    private static function datesWithoutSeparators(string $password): array
    {
        $matches = [];
        $length = mb_strlen($password);

        // Dates without separators are between length 4 "1191" and 8 "11111991".
        for ($begin = 0; $begin < $length; ++$begin) {
            for ($end = $begin + 3; $end - $begin <= 7 && $end < $length; ++$end) {
                $token = mb_substr($password, $begin, $end - $begin + 1);

                if (!preg_match(self::DATE_NO_SEPARATOR, $token)) {
                    continue;
                }

                $candidates = [];

                $possibleSplits = self::$DATE_SPLITS[mb_strlen($token)];
                foreach ($possibleSplits as $splitPositions) {
                    $day = MultibyteNumber::convertToInteger(mb_substr($token, 0, $splitPositions[0]));
                    $month = MultibyteNumber::convertToInteger(mb_substr($token, $splitPositions[0], $splitPositions[1] - $splitPositions[0]));
                    $year = MultibyteNumber::convertToInteger(mb_substr($token, $splitPositions[1]));

                    $date = self::mapIntegersToDate([$day, $month, $year]);
                    if ($date) {
                        $candidates[] = $date;
                    }
                }

                if (empty($candidates)) {
                    continue;
                }

                // Find the best candidate in the different possible dmy mappings for the same substring.
                $bestCandidate = self::getBestCandidate($candidates);

                $matches[] = new DateMatch(
                    password: $password,
                    begin: $begin,
                    end: $end,
                    token: $token,
                    day: $bestCandidate->day,
                    month: $bestCandidate->month,
                    year: $bestCandidate->year,
                    separator: ''
                );
            }
        }

        return $matches;
    }

    /**
     * Match the candidate date that likely takes the fewest guesses: a year closest to the current year.
     * i.e., considering "111524", prefer 11-15-24 to 1-1-1524 (interpreting "24" as 2024).
     *
     * @param DateResult[] $candidates
     */
    private static function getBestCandidate(array $candidates): DateResult
    {
        $referenceYear = Now::getYear();

        $bestCandidate = $candidates[0];
        $minDistance = self::getDistanceForMatchCandidate($bestCandidate, $referenceYear);

        foreach ($candidates as $candidate) {
            $distance = self::getDistanceForMatchCandidate($candidate, $referenceYear);
            if ($distance < $minDistance) {
                $bestCandidate = $candidate;
                $minDistance = $distance;
            }
        }

        return $bestCandidate;
    }

    /**
     * Find dates with separators in a password.
     *
     * @return DateMatch[]
     */
    private static function datesWithSeparators(string $password): array
    {
        $matches = [];
        $length = mb_strlen($password);

        // Dates with separators are between length 6 "1/1/91" and 10 "11/11/1991".
        for ($begin = 0; $begin < $length; ++$begin) {
            for ($end = $begin + 5; $end - $begin <= 9 && $end < $length; ++$end) {
                $token = mb_substr($password, $begin, $end - $begin + 1);

                if (!preg_match(self::DATE_WITH_SEPARATOR, $token, $captures)) {
                    continue;
                }

                $date = self::mapIntegersToDate([
                    MultibyteNumber::convertToInteger($captures[1]),
                    MultibyteNumber::convertToInteger($captures[3]),
                    MultibyteNumber::convertToInteger($captures[4]),
                ]);
                if (!$date) {
                    continue;
                }

                $matches[] = new DateMatch(
                    password: $password,
                    begin: $begin,
                    end: $end,
                    token: $token,
                    day: $date->day,
                    month: $date->month,
                    year: $date->year,
                    separator: $captures[2]
                );
            }
        }

        return $matches;
    }

    /**
     * Removes date matches that are strict substrings of others.
     *
     * This is helpful because the match function will contain matches for all valid date strings in a way that is
     * tricky to capture with regexes only. While thorough, it will contain some unintuitive noise:
     *
     *   '2015_06_04', in addition to matching 2015_06_04, will also contain
     *   5(!) other date matches: 15_06_04, 5_06_04, ..., even 2015 (matched as 5/1/2020)
     *
     * @param DateMatch[] $matches
     *
     * @return DateMatch[] the provided array of matches, but with matches that are strict substrings of others removed
     */
    private static function removeRedundantMatches(array $matches): array
    {
        return array_filter($matches, static function (DateMatch $match) use ($matches): bool {
            foreach ($matches as $otherMatch) {
                if ($match === $otherMatch) {
                    continue;
                }
                if ($otherMatch->begin() <= $match->begin() && $otherMatch->end() >= $match->end()) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * @param int[] $integers three numbers in an array representing day, month and year (not necessarily in that order)
     */
    private static function mapIntegersToDate(array $integers): ?DateResult
    {
        // Given a 3-tuple, discard if:
        // - any int is over two digits but under the min allowable year,
        // - any int is over the max allowable year.
        $invalidYear = \count(array_filter($integers, static fn (int $int): bool => ($int > 99 && $int < self::MIN_YEAR) || $int > self::MAX_YEAR));
        if ($invalidYear > 0) {
            return null;
        }

        // First look for a four digit year: yyyy + daymonth or daymonth + yyyy.
        $possibleYearSplits = [
            [$integers[2], [$integers[0], $integers[1]]], // year last
            [$integers[0], [$integers[1], $integers[2]]], // year first
        ];

        foreach ($possibleYearSplits as [$year, $rest]) {
            if ($year >= self::MIN_YEAR && $year <= self::MAX_YEAR) {
                if ($dm = self::mapIntegersToDayMonth($rest)) {
                    return new DateResult(
                        day: $dm->day,
                        month: $dm->month,
                        year: $year
                    );
                }

                // For a candidate that includes a four-digit year,
                // when the remaining integers don't match to a day and month, it is not a date.
                return null;
            }
        }

        foreach ($possibleYearSplits as [$year, $rest]) {
            if ($dm = self::mapIntegersToDayMonth($rest)) {
                return new DateResult(
                    day: $dm->day,
                    month: $dm->month,
                    year: self::twoToFourDigitYear($year),
                );
            }
        }

        return null;
    }

    /**
     * @param int[] $integers two numbers in an array representing day and month (not necessarily in that order)
     */
    private static function mapIntegersToDayMonth(array $integers): ?DayMonthResult
    {
        foreach ([$integers, array_reverse($integers)] as [$day, $month]) {
            if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12) {
                return new DayMonthResult(
                    day: $day,
                    month: $month,
                );
            }
        }

        return null;
    }

    /**
     * @param int $year a two-digit number representing a year
     *
     * @return int the most likely four digit year for the provided number
     */
    private static function twoToFourDigitYear(int $year): int
    {
        if ($year > 50) {
            // 87 -> 1987
            return $year + 1900;
        }

        // 25 -> 2025
        return $year + 2000;
    }

    /**
     * @return int number of years between the detected year and the current year for a candidate
     */
    private static function getDistanceForMatchCandidate(DateResult $candidate, int $referenceYear): int
    {
        return abs($candidate->year - $referenceYear);
    }
}
