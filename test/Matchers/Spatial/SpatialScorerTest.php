<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Spatial;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Spatial\SpatialMatch;
use ZxcvbnPhp\Matchers\Spatial\SpatialScorer;
use ZxcvbnPhp\Math\Binomial;

#[CoversClass(SpatialScorer::class)]
final class SpatialScorerTest extends TestCase
{
    public function testGuessesBasic(): void
    {
        $token = 'zxcvbn';
        $match = new SpatialMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            graph: 'qwerty',
            shiftedCount: 0,
            turns: 1,
        );

        self::assertEqualsWithDelta(
            $this->getBaseGuessCount($token),
            SpatialScorer::getGuesses($match, Configurator::getOptions(new Config())),
            1e-11,
            'with no turns or shifts, guesses is starts * degree * (len-1)'
        );
    }

    public function testGuessesShifted(): void
    {
        $token = 'ZxCvbn';
        $match = new SpatialMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            graph: 'qwerty',
            shiftedCount: 2,
            turns: 1,
        );

        self::assertEqualsWithDelta(
            $this->getBaseGuessCount($token) * (Binomial::binom(6, 2) + Binomial::binom(6, 1)),
            SpatialScorer::getGuesses($match, Configurator::getOptions(new Config())),
            1e-11,
            'guesses is added for shifted keys, similar to capitals in dictionary matching'
        );
    }

    public function testGuessesEverythingShifted(): void
    {
        $token = 'ZXCVBN';
        $match = new SpatialMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            graph: 'qwerty',
            shiftedCount: 6,
            turns: 1,
        );

        self::assertEqualsWithDelta(
            $this->getBaseGuessCount($token) * 2,
            SpatialScorer::getGuesses($match, Configurator::getOptions(new Config())),
            1e-11,
            'when everything is shifted, guesses are double'
        );
    }

    public static function provideGuessesComplexCaseCases(): iterable
    {
        return [
            ['6yhgf',        2, 19596],
            ['asde3w',       3, 203315],
            ['zxcft6yh',     3, 558460],
            ['xcvgy7uj',     3, 558460],
            ['ertghjm,.',    5, 30160744],
            ['qwerfdsazxcv', 5, 175281377],
        ];
    }

    #[DataProvider('provideGuessesComplexCaseCases')]
    public function testGuessesComplexCase(string $token, int $turns, float $expected): void
    {
        $match = new SpatialMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            graph: 'qwerty',
            shiftedCount: 0,
            turns: $turns,
        );

        $actual = SpatialScorer::getGuesses($match, Configurator::getOptions(new Config()));
        self::assertIsFloat($actual);

        self::assertEqualsWithDelta(
            $expected,
            $actual,
            1.0,
            'spatial guesses accounts for turn positions, directions and starting keys'
        );
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Spatial\SpatialMatch');

        SpatialScorer::getGuesses(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('spatial', SpatialScorer::getPattern());
    }

    private function getBaseGuessCount(string $token): float
    {
        // qwerty keyboard starting position * qwerty keyboard average degree * (length - 1)
        // - 1 term because not counting spatial patterns of length 1
        // e.g. for length 6, multiplier is 5 for needing to try len2, len3, ..., len6
        return 94 * 432 / 94 * (\strlen($token) - 1);
    }
}
