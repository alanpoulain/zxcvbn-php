<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Dictionary;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\AbstractMatch;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryScorer;
use ZxcvbnPhp\Matchers\Dictionary\L33tExtraMatch;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tChangeResult;
use ZxcvbnPhp\Matchers\Dictionary\Variants\L33tVariant;
use ZxcvbnPhp\Matchers\Dictionary\Variants\UppercaseVariant;

#[CoversClass(DictionaryScorer::class)]
#[CoversClass(L33tVariant::class)]
#[CoversClass(UppercaseVariant::class)]
#[CoversClass(AbstractMatch::class)]
#[CoversClass(DictionaryMatch::class)]
final class DictionaryScorerTest extends TestCase
{
    public function testGuessesBaseRank(): void
    {
        $match = new DictionaryMatch(
            password: 'aaaaa',
            begin: 0,
            end: 5,
            token: 'aaaaaa',
            matchedWord: 'aaaaa',
            rank: 32,
            dictionaryName: 'dic',
            reversed: false,
            l33t: false,
            levenshteinDistance: -1
        );

        self::assertSame(32.0, DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())), 'base guesses == the rank');
    }

    public function testGuessesCapitalization(): void
    {
        $match = new DictionaryMatch(
            password: 'AAAaaa',
            begin: 0,
            end: 5,
            token: 'AAAaaa',
            matchedWord: 'AAAaaa',
            rank: 32,
            dictionaryName: 'dic',
            reversed: false,
            l33t: false,
            levenshteinDistance: -1
        );

        $expected = 32.0 * 41; // rank * uppercase variations
        self::assertSame($expected, DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())), 'extra guesses are added for capitalization');
    }

    public static function provideGuessesUppercaseVariationsCases(): iterable
    {
        return [
            ['',              1],
            ['a',             1],
            ['A',             2],
            ['abcdef',        1],
            ['Abcdef',        2],
            ['1A2b3c4d5e6f7', 2],
            ['abcdeF',        2],
            ['1a2b3c4d5e6F7', 2],
            ['ABCDEF',        2],
            ['1A2B3C4D5E6F7', 2],
            ['aBcdef',        6],  // nCk(6, 1)
            ['aBcDef',        21], // nCk(6, 1) + nCk(6, 2)
            ['ABCDEf',        6],  // nCk(6, 1)
            ['aBCDEf',        21], // nCk(6, 1) + nCk(6, 2)
            ['ABCdef',        41], // nCk(6, 1) + nCk(6, 2) + nCk(6, 3)
        ];
    }

    #[DataProvider('provideGuessesUppercaseVariationsCases')]
    public function testGuessesUppercaseVariations(string $token, float $expectedGuesses): void
    {
        $match = new DictionaryMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            matchedWord: $token,
            rank: 1,
            dictionaryName: 'dic',
            reversed: false,
            l33t: false,
            levenshteinDistance: -1
        );

        self::assertSame(
            $expectedGuesses,
            DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())),
            "guess multiplier of {$token} is {$expectedGuesses}"
        );
    }

    public function testGuessesReversed(): void
    {
        $match = new DictionaryMatch(
            password: 'aaa',
            begin: 0,
            end: 2,
            token: 'aaa',
            matchedWord: 'aaa',
            rank: 32,
            dictionaryName: 'dic',
            reversed: true,
            l33t: false,
            levenshteinDistance: -1
        );

        $expected = 32.0 * 2; // rank * reversed
        self::assertSame($expected, DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())), 'guesses are doubled when word is reversed');
    }

    public function testGuessesL33t(): void
    {
        $match = new DictionaryMatch(
            password: 'aaa@@@',
            begin: 0,
            end: 5,
            token: 'aaa@@@',
            matchedWord: 'aaaaaa',
            rank: 32,
            dictionaryName: 'dic',
            reversed: false,
            l33t: true,
            levenshteinDistance: -1,
            l33tExtra: new L33tExtraMatch(changes: [
                new L33tChangeResult(3, 'a', '@'),
                new L33tChangeResult(4, 'a', '@'),
                new L33tChangeResult(5, 'a', '@'),
            ], changesDisplay: '[3] @ -> a, [4] @ -> a, [5] @ -> a')
        );

        $expected = 32.0 * 41; // rank * l33t variations (nCk(6, 3) + nCk(6, 2) + nCk(6, 1))
        self::assertSame($expected, DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())), 'extra guesses are added for common l33t substitutions');
    }

    public function testGuessesL33tAndUppercased(): void
    {
        $match = new DictionaryMatch(
            password: 'AaA@@@',
            begin: 0,
            end: 5,
            token: 'AaA@@@',
            matchedWord: 'aaaaaa',
            rank: 32,
            dictionaryName: 'dic',
            reversed: false,
            l33t: true,
            levenshteinDistance: -1,
            l33tExtra: new L33tExtraMatch(changes: [
                new L33tChangeResult(3, 'a', '@'),
                new L33tChangeResult(4, 'a', '@'),
                new L33tChangeResult(5, 'a', '@'),
            ], changesDisplay: '[3] @ -> a, [4] @ -> a, [5] @ -> a')
        );

        $expected = 32.0 * 41 * 3; // rank * l33t variations (nCk(6, 3) + nCk(6, 2) + nCk(6, 1)) * uppercase variations
        self::assertSame(
            $expected,
            DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())),
            'extra guesses are added for both capitalization and common l33t substitutions'
        );
    }

    public static function provideGuessesL33tVariationsCases(): iterable
    {
        return [
            ['',  1, []],
            ['a', 1, []],
            ['4', 2, [new L33tChangeResult(0, 'a', '4')]],
            ['4pple', 2, [new L33tChangeResult(0, 'a', '4')]],
            ['abcet', 1, []],
            ['4bcet', 2, [new L33tChangeResult(0, 'a', '4')]],
            ['a8cet', 2, [new L33tChangeResult(1, 'b', '8')]],
            ['abce+', 2, [new L33tChangeResult(4, 't', '+')]],
            ['48cet', 4, [new L33tChangeResult(0, 'a', '4'), new L33tChangeResult(1, 'b', '8')]],
            ['a4a4aa', /* nCk(6, 2) */ 15 + /* nCk(6, 1) */ 6, [new L33tChangeResult(1, 'a', '4'), new L33tChangeResult(3, 'a', '4')]],
            ['4a4a44', /* nCk(6, 2) */ 15 + /* nCk(6, 1) */ 6, [new L33tChangeResult(0, 'a', '4'), new L33tChangeResult(2, 'a', '4'), new L33tChangeResult(4, 'a', '4'), new L33tChangeResult(5, 'a', '4')]],
            ['a44att+', (/* nCk(4, 2) */ 6 + /* nCk(4, 1) */ 4) * /* nCk(3, 1) */ 3, [new L33tChangeResult(1, 'a', '4'), new L33tChangeResult(2, 'a', '4'), new L33tChangeResult(6, 't', '+')]],
            ['wwwpassvvord', /* nCk(4, 1) */ 4, [new L33tChangeResult(7, 'w', 'vv')]],
        ];
    }

    /**
     * @param L33tChangeResult[] $changes
     */
    #[DataProvider('provideGuessesL33tVariationsCases')]
    public function testGuessesL33tVariations(string $token, float $expectedGuesses, array $changes): void
    {
        $match = new DictionaryMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            matchedWord: '',
            rank: 1,
            dictionaryName: 'dic',
            reversed: false,
            l33t: true,
            levenshteinDistance: -1,
            l33tExtra: new L33tExtraMatch(changes: $changes, changesDisplay: '')
        );

        self::assertSame(
            $expectedGuesses,
            DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())),
            "extra l33t guesses of {$token} is {$expectedGuesses}"
        );
    }

    public function testCapitalisationNotAffectingL33t(): void
    {
        $token = 'Aa44aA';
        $match = new DictionaryMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            matchedWord: 'aaaaaa',
            rank: 1,
            dictionaryName: 'dic',
            reversed: false,
            l33t: true,
            levenshteinDistance: -1,
            l33tExtra: new L33tExtraMatch(changes: [
                new L33tChangeResult(2, 'a', '4'),
                new L33tChangeResult(3, 'a', '4'),
            ], changesDisplay: '[2] 4 -> a, [3] 4 -> a')
        );

        $expected = 15.0 + 6; // nCk(6, 2) + nCk(6, 1)
        self::assertSame(
            $expected,
            L33tVariant::getVariations($match),
            "capitalization doesn't affect extra l33t guesses calc"
        );
    }

    public function testGuessesDiceware(): void
    {
        $match = new DictionaryMatch(
            password: 'AaA@@@',
            begin: 0,
            end: 5,
            token: 'AaA@@@',
            matchedWord: 'AaA@@@',
            rank: 32,
            dictionaryName: 'common-diceware',
            reversed: false,
            l33t: false,
            levenshteinDistance: -1
        );
        $expected = 3888.0;

        self::assertSame($expected, DictionaryScorer::getGuesses($match, Configurator::getOptions(new Config())), 'special guesses for diceware');
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch');

        DictionaryScorer::getGuesses(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('dictionary', DictionaryScorer::getPattern());
    }
}
