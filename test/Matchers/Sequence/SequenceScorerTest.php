<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Sequence;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatch;
use ZxcvbnPhp\Matchers\Sequence\SequenceScorer;

#[CoversClass(SequenceScorer::class)]
final class SequenceScorerTest extends TestCase
{
    public static function provideGuessesCases(): iterable
    {
        return [
            ['ab',   true,  4 * 2],      // obvious start * len-2
            ['XYZ',  true,  26 * 3],     // base26 * len-3
            ['4567', true,  10 * 4],     // base10 * len-4
            ['7654', false, 10 * 4 * 2], // base10 * len-4 * descending
            ['ZYX',  false, 4 * 3 * 2],  // obvious start * len-3 * descending
        ];
    }

    #[DataProvider('provideGuessesCases')]
    public function testGuesses(string $token, bool $ascending, float $expectedGuesses): void
    {
        $match = new SequenceMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            sequenceName: 'lower',
            sequenceSpace: 2155,
            ascending: $ascending
        );

        self::assertSame(
            $expectedGuesses,
            SequenceScorer::getGuesses($match, Configurator::getOptions(new Config())),
            "the sequence pattern '{$token}' has guesses of {$expectedGuesses}"
        );
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Sequence\SequenceMatch');

        SequenceScorer::getGuesses(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('sequence', SequenceScorer::getPattern());
    }
}
