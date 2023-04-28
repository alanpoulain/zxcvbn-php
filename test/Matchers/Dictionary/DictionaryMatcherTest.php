<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Dictionary;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatcher;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(DictionaryMatcher::class)]
final class DictionaryMatcherTest extends AbstractMatchTestCase
{
    private static array $testDicts = [
        'd1' => [
            'motherboard',
            'mother',
            'board',
            'αβγδ',
            'γδεζ',
        ],
        'd2' => [
            'z',
            '8',
            '99',
            '$',
            'asdf1234&*',
        ],
    ];

    public function testDefaultDictionary(): void
    {
        $password = 'if';
        $patterns = [$password, $password];

        $this->checkMatches(
            'default dictionaries',
            DictionaryMatcher::match($password, Configurator::getOptions(new Config())),
            'dictionary',
            $patterns,
            [[0, 1], [0, 1]],
            [
                'matchedWord' => $patterns,
                'rank' => [33, 201],
                'dictionaryName' => ['en-commonWords', 'en-wikipedia'],
            ]
        );
    }

    /**
     * @return string[][]
     */
    public static function provideWordsNotInDictionaryCases(): iterable
    {
        return [
            ['jzj'],
            ['kqzqw'],
        ];
    }

    #[DataProvider('provideWordsNotInDictionaryCases')]
    public function testWordsNotInDictionary(string $password): void
    {
        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config()));

        self::assertEmpty($matches, 'does not match non-dictionary words');
    }

    public function testContainingWords(): void
    {
        $password = 'motherboard';
        $patterns = ['mother', 'motherboard', 'board'];

        $this->checkMatches(
            "matches words that contain other words: {$password}",
            DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            'dictionary',
            $patterns,
            [[0, 5], [0, 10], [6, 10]],
            [
                'matchedWord' => $patterns,
                'rank' => [2, 1, 3],
                'dictionaryName' => ['d1', 'd1', 'd1'],
            ]
        );
    }

    public function testOverlappingWords(): void
    {
        $password = 'αβγΔεζ';
        $patterns = ['αβγΔ', 'γΔεζ'];
        $matchedWords = ['αβγδ', 'γδεζ'];

        $this->checkMatches(
            'matches multiple words when they overlap',
            DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            'dictionary',
            $patterns,
            [[0, 3], [2, 5]],
            [
                'matchedWord' => $matchedWords,
                'rank' => [4, 5],
                'dictionaryName' => ['d1', 'd1'],
                'reversed' => [false, false],
                'l33T' => [false, false],
            ]
        );
    }

    public function testUppercasingIgnored(): void
    {
        $password = 'BoaRdZ';
        $patterns = ['BoaRd', 'Z'];

        $this->checkMatches(
            'ignores uppercasing',
            DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            'dictionary',
            $patterns,
            [[0, 4], [5, 5]],
            [
                'matchedWord' => ['board', 'z'],
                'rank' => [3, 1],
                'dictionaryName' => ['d1', 'd2'],
            ]
        );
    }

    public function testWordsSurroundedByNonWords(): void
    {
        $prefixes = ['q', '%%'];
        $suffixes = ['%', 'qq'];
        $pattern = 'asdf1234&*';

        foreach ($this->generatePasswords($pattern, $prefixes, $suffixes) as [$password, $begin, $end]) {
            $this->checkMatches(
                'identifies words surrounded by non-words',
                DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
                'dictionary',
                [$pattern],
                [[$begin, $end]],
                [
                    'matchedWord' => [$pattern],
                    'rank' => [5],
                    'dictionaryName' => ['d2'],
                ]
            );
        }
    }

    public function testAllDictionaryWords(): void
    {
        foreach (self::$testDicts as $dictionaryName => $dict) {
            foreach ($dict as $index => $word) {
                $word = (string) $word;

                if ('motherboard' === $word) {
                    continue; // skip words that contain others
                }

                $this->checkMatches(
                    'matches against all words in provided dictionaries',
                    DictionaryMatcher::match($word, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
                    'dictionary',
                    [$word],
                    [[0, mb_strlen($word) - 1]],
                    [
                        'matchedWord' => [$word],
                        'rank' => [$index + 1],
                        'dictionaryName' => [$dictionaryName],
                    ]
                );
            }
        }
    }

    public function testUserProvidedInput(): void
    {
        $password = 'foobar';
        $patterns = ['foo', 'bar'];

        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config()), ['foo', 'bar']);
        $matches = array_values(array_filter($matches, static fn (DictionaryMatch $match) => 'userInputs' === $match->dictionaryName()));

        $this->checkMatches(
            'matches with provided user input dictionary',
            $matches,
            'dictionary',
            $patterns,
            [[0, 2], [3, 5]],
            [
                'matchedWord' => ['foo', 'bar'],
                'rank' => [1, 2],
            ]
        );
    }

    public function testUserProvidedInputInNoOtherDictionary(): void
    {
        $password = '39kq9.1x0!3n6';

        $this->checkMatches(
            'matches with provided user input dictionary',
            DictionaryMatcher::match($password, Configurator::getOptions(new Config()), [$password]),
            'dictionary',
            [$password],
            [[0, 12]],
            [
                'matchedWord' => [$password],
                'rank' => [1],
            ]
        );
    }

    public function testMatchesInMultipleDictionaries(): void
    {
        $password = 'pass';

        $this->checkMatches(
            'matches words in multiple dictionaries',
            DictionaryMatcher::match($password, Configurator::getOptions(new Config())),
            'dictionary',
            ['pa', 'pa', 'pas', 'pas', 'pass', 'pass', 'pass', 'a', 'as', 'ass', 'ass', 'ass', 'ss'],
            [[0, 1], [0, 1], [0, 2], [0, 2], [0, 3], [0, 3], [0, 3], [1, 1], [1, 2], [1, 3], [1, 3], [1, 3], [2, 3]],
            [
                'dictionaryName' => ['en-commonWords', 'en-wikipedia', 'en-commonWords', 'en-wikipedia', 'common-passwords', 'en-commonWords', 'en-wikipedia', 'common-passwords', 'en-wikipedia', 'common-passwords', 'en-commonWords', 'en-wikipedia', 'en-wikipedia'],
            ]
        );
    }

    public function testMatchUsingLevenshteinDistance(): void
    {
        $password = 'ishduehlduod83h4mfs8';

        $this->checkMatches(
            'matches words using Levenshtein distance',
            DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], useLevenshteinDistance: true)), ['ishduehgldueod83h4mfis8']),
            'dictionary',
            ['ishduehlduod83h4mfs8'],
            [[0, 19]],
            [
                'levenshteinDistance' => [3],
                'matchedWord' => ['ishduehgldueod83h4mfis8'],
                'dictionaryName' => ['userInputs'],
            ]
        );
    }

    public function testMatchMistypedCommonEnglishWord(): void
    {
        $password = 'alaphant';

        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config(useLevenshteinDistance: true)));
        $matches = array_values(array_filter($matches, static fn (DictionaryMatch $match) => -1 !== $match->levenshteinDistance()));

        $this->checkMatches(
            'matches mistyped common English word using Levenshtein distance',
            [$matches[0]],
            'dictionary',
            ['alaphant'],
            [[0, 7]],
            [
                'levenshteinDistance' => [2],
                'matchedWord' => ['elephant'],
            ]
        );
    }

    public function testRespectLevenshteinThreshold(): void
    {
        $password = 'εεlεεphααnt';

        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: ['d' => ['εlεphαnt']], useLevenshteinDistance: true, levenshteinThreshold: 2)));
        $matches = array_values(array_filter($matches, static fn (DictionaryMatch $match) => -1 !== $match->levenshteinDistance()));

        self::assertEmpty($matches, 'respect Levenshtein threshold');
    }

    public function testRespectHighLevenshteinThreshold(): void
    {
        $password = 'εεlεεphααnt';

        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: ['d' => ['εlεphαnt']], useLevenshteinDistance: true, levenshteinThreshold: 3)));
        $matches = array_values(array_filter($matches, static fn (DictionaryMatch $match) => -1 !== $match->levenshteinDistance()));

        self::assertNotEmpty($matches);
    }

    public function testLevenshteinThresholdPasswordTooShortOneCharacter(): void
    {
        $password = 'a';

        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: ['d' => ['b']], useLevenshteinDistance: true, levenshteinThreshold: 0)));
        $matches = array_values(array_filter($matches, static fn (DictionaryMatch $match) => -1 !== $match->levenshteinDistance()));

        self::assertNotEmpty($matches);
    }

    public function testLevenshteinThresholdPasswordTooShort(): void
    {
        $password = 'ilogicaIIy';

        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: ['d' => ['illogically']], useLevenshteinDistance: true, levenshteinThreshold: 2)));
        $matches = array_values(array_filter($matches, static fn (DictionaryMatch $match) => -1 !== $match->levenshteinDistance()));

        self::assertNotEmpty($matches);
    }

    public function testLevenshteinThresholdLongerThanPassword(): void
    {
        $password = 'tire';

        $matches = DictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: ['d' => ['bun']], useLevenshteinDistance: true, levenshteinThreshold: 4)));
        $matches = array_values(array_filter($matches, static fn (DictionaryMatch $match) => -1 !== $match->levenshteinDistance()));

        self::assertEmpty($matches);
    }

    public function testGetPattern(): void
    {
        self::assertSame('dictionary', DictionaryMatcher::getPattern());
    }
}
