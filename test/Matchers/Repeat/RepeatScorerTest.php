<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Repeat;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Repeat\RepeatMatch;
use ZxcvbnPhp\Matchers\Repeat\RepeatScorer;
use ZxcvbnPhp\Scorer;

#[CoversClass(AbstractScorer::class)]
#[CoversClass(RepeatScorer::class)]
final class RepeatScorerTest extends TestCase
{
    public static function provideGuessesCases(): iterable
    {
        return [
            ['aa',   'a',  2,  24],
            ['999',  '9',  3,  36],
            ['$$$$', '$',  4,  48],
            ['abab', 'ab', 2,  18],
            ['batterystaplebatterystaplebatterystaple', 'batterystaple', 3,  66561456],
        ];
    }

    #[DataProvider('provideGuessesCases')]
    public function testGuesses(string $token, string $repeatedChar, int $repeatCount, float $expectedGuesses): void
    {
        $scorer = new Scorer();
        $matcher = new Matcher();
        $baseAnalysis = $scorer->getMostGuessableMatchSequence($repeatedChar, $matcher->getMatches($repeatedChar));
        $baseGuesses = $baseAnalysis->guesses;

        $match = new RepeatMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            baseMatches: [],
            baseGuesses: $baseGuesses,
            repeatCount: $repeatCount,
            repeatedChar: $repeatedChar
        );

        self::assertSame($expectedGuesses, RepeatScorer::getGuesses($match, Configurator::getOptions(new Config())), "the repeat pattern {$token} has guesses of {$expectedGuesses}");
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Repeat\RepeatMatch');

        RepeatScorer::getGuesses(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('repeat', RepeatScorer::getPattern());
    }
}
