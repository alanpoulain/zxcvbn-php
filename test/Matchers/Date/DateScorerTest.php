<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Date;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Date\Now;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Date\DateMatch;
use ZxcvbnPhp\Matchers\Date\DateScorer;

#[CoversClass(DateScorer::class)]
#[CoversClass(Now::class)]
final class DateScorerTest extends TestCase
{
    public function testGuessesBaseRank(): void
    {
        $token = '1123';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: 1,
            month: 1,
            year: 1923,
            separator: ''
        );

        $expected = 365.0 * abs(Now::getYear() - $match->year());
        self::assertSame($expected, DateScorer::getGuesses($match, Configurator::getOptions(new Config())), "guesses for {$token} is 365 * distance from reference year");
    }

    public function testGuessMinYearSpace(): void
    {
        $token = '112020';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: 1,
            month: 1,
            year: 2020,
            separator: ''
        );

        $expected = 7300.0; // 365 * DateMatch::MIN_YEAR_SPACE;
        self::assertSame($expected, DateScorer::getGuesses($match, Configurator::getOptions(new Config())), 'recent years assume MIN_YEAR_SPACE');
    }

    public function testGuessWithSeparator(): void
    {
        $token = '1/1/2020';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: 1,
            month: 1,
            year: 2020,
            separator: '/'
        );

        $expected = 29200.0; // 365 * DateMatch::MIN_YEAR_SPACE * 4;
        self::assertSame($expected, DateScorer::getGuesses($match, Configurator::getOptions(new Config())), 'extra guesses are added for separators');
    }

    public function testGuessesPastYear(): void
    {
        $token = '1972';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: -1,
            month: -1,
            year: 1972,
            separator: ''
        );

        self::assertSame(
            (float) (Now::getYear() - (int) $token),
            DateScorer::getGuesses($match, Configurator::getOptions(new Config())),
            'guesses of (year - reference year) for past year matches'
        );
    }

    public function testGuessesFutureYear(): void
    {
        $token = '2050';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: -1,
            month: -1,
            year: 2050,
            separator: ''
        );

        self::assertSame(
            (float) ((int) $token - Now::getYear()),
            DateScorer::getGuesses($match, Configurator::getOptions(new Config())),
            'guesses of (year - reference year) for future year matches'
        );
    }

    public function testGuessesUnderMinimumYearSpace(): void
    {
        $token = '2020';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: -1,
            month: -1,
            year: 2020,
            separator: ''
        );

        self::assertSame(
            20.0, // DateMatch::MIN_YEAR_SPACE
            DateScorer::getGuesses($match, Configurator::getOptions(new Config())),
            'guesses of MIN_YEAR_SPACE for a year close to reference year'
        );
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Date\DateMatch');

        DateScorer::getGuesses(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('date', DateScorer::getPattern());
    }
}
