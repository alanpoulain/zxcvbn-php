<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Dictionary;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Dictionary\L33tExtraMatch;
use ZxcvbnPhp\Matchers\Dictionary\L33tMatcher;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tChangeResult;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tExtraResult;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tResult;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(L33tMatcher::class)]
#[CoversClass(L33tExtraMatch::class)]
#[CoversClass(L33tChangeResult::class)]
#[CoversClass(L33tExtraResult::class)]
#[CoversClass(L33tResult::class)]
final class L33tMatcherTest extends AbstractMatchTestCase
{
    private static array $testDicts = [
        'words' => [
            'aac',
            'password',
            'paassword',
            '4sdfo',
            'computer',
            'pacific',
            'mariel',
            'ariel',
            'aabccccvedggfiiviosstlxzvaabccccvedggfiiviosstlx2',
            'aabccccvedggfiiviosstlxzvaabccccvedggfiiviosstlx2/',
            'λ',
            'όγο',
            'λόγοσλ',
            'part',
            'parti4l',
            'iiii',
            'uwu',
            'bTbbb8',
        ],
        'words2' => [
            'cgo',
        ],
    ];

    private static array $testTable = [
        'a' => ['4', '@'],
        'c' => ['(', '((', '{', '[', '<'],
        'g' => ['6', '9'],
        'o' => ['0', '()'],
        'u' => ['|_|'],
        'fi' => ['ﬁ'],
        'λ' => ['𐒰'],
    ];

    public function testEmptyString(): void
    {
        self::assertSame(
            [],
            L33tMatcher::match('', Configurator::getOptions(new Config())),
            "doesn't match empty string"
        );
    }

    public function testSingleCharacter(): void
    {
        self::assertSame(
            [],
            L33tMatcher::match('4', Configurator::getOptions(new Config())),
            "doesn't match single character"
        );
    }

    public function testPureDictionaryWords(): void
    {
        self::assertSame(
            [],
            L33tMatcher::match('password', Configurator::getOptions(new Config())),
            "doesn't match pure dictionary words"
        );
    }

    public function testPureDictionaryWordsWithL33tCharactersAfter(): void
    {
        self::assertSame(
            [],
            L33tMatcher::match('password4', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            "doesn't match pure dictionary word with l33t characters after"
        );
    }

    public function testCapitalizedDictionaryWordsWithL33tCharactersAfter(): void
    {
        self::assertSame(
            [],
            L33tMatcher::match('Password4', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            "doesn't match capitalized dictionary word with l33t characters after"
        );
    }

    public function testSingleCharacterL33tWords(): void
    {
        self::assertSame(
            [],
            L33tMatcher::match('4 1 @', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            "doesn't match single-character l33ted words"
        );
    }

    public function testSubstitutionSubsets(): void
    {
        // For long inputs, trying every subset of every possible substitution could quickly get large.
        // In this example, with the max sub set to 1: 4 -> a, 0 -> O is detected as a possible sub (full sub),
        // 4 -> a is also tried (partial sub), but the subset 0 -> O isn't tried, missing the match for 4sdfo.

        self::assertSame(
            [],
            L33tMatcher::match('4sdf0', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tTable: self::$testTable, l33tMaxSubstitutions: 1))),
            "doesn't match with subsets of possible l33t substitutions"
        );
    }

    /**
     * The character '1' can map to both 'i' and 'l' - there was previously a bug that prevented it from matching
     * against the latter.
     */
    public function testSubstitutionOfCharacterL(): void
    {
        $this->checkMatches(
            'matches against overlapping l33t patterns',
            L33tMatcher::match('marie1', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            'dictionary',
            ['marie1', 'arie1'],
            [[0, 5], [1, 5]],
            [
                'l33t' => [true, true],
                'l33tExtra' => [
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 5, letter: 'l', substitution: '1'),
                    ], changesDisplay: '[5] 1 -> l'),
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 5, letter: 'l', substitution: '1'),
                    ], changesDisplay: '[5] 1 -> l'),
                ],
                'matchedWord' => ['mariel', 'ariel'],
            ]
        );
    }

    public static function provideCommonL33tSubstitutionsCases(): iterable
    {
        return [
            [
                'password' => 'p4ssword',
                'pattern' => 'p4ssword',
                'word' => 'password',
                'dictionary' => 'words',
                'rank' => 2,
                'beginEnds' => [0, 7],
                'changes' => [new L33tChangeResult(index: 1, letter: 'a', substitution: '4')],
                'changesDisplay' => '[1] 4 -> a',
            ],
            [
                'password' => 'p@@ssw0rd',
                'pattern' => 'p@@ssw0rd',
                'word' => 'paassword',
                'dictionary' => 'words',
                'rank' => 3,
                'beginEnds' => [0, 8],
                'changes' => [
                    new L33tChangeResult(index: 1, letter: 'a', substitution: '@'),
                    new L33tChangeResult(index: 2, letter: 'a', substitution: '@'),
                    new L33tChangeResult(index: 6, letter: 'o', substitution: '0'),
                ],
                'changesDisplay' => '[1] @ -> a, [2] @ -> a, [6] 0 -> o',
            ],
            [
                'password' => '|_|(()mp|_|ter',
                'pattern' => '(()mp|_|ter',
                'word' => 'computer',
                'dictionary' => 'words',
                'rank' => 5,
                'beginEnds' => [3, 13],
                'changes' => [
                    new L33tChangeResult(index: 3, letter: 'c', substitution: '('),
                    new L33tChangeResult(index: 4, letter: 'o', substitution: '()'),
                    new L33tChangeResult(index: 7, letter: 'u', substitution: '|_|'),
                ],
                'changesDisplay' => '[3] ( -> c, [4] () -> o, [7] |_| -> u',
            ],
            [
                'password' => 'ﬁp@ciﬁc',
                'pattern' => 'p@ciﬁc',
                'word' => 'pacific',
                'dictionary' => 'words',
                'rank' => 6,
                'beginEnds' => [1, 6],
                'changes' => [
                    new L33tChangeResult(index: 2, letter: 'a', substitution: '@'),
                    new L33tChangeResult(index: 5, letter: 'fi', substitution: 'ﬁ'),
                ],
                'changesDisplay' => '[2] @ -> a, [5] ﬁ -> fi',
            ],
            [
                'password' => 'aSdfO{G0asDfO',
                'pattern' => '{G0',
                'word' => 'cgo',
                'dictionary' => 'words2',
                'rank' => 1,
                'beginEnds' => [5, 7],
                'changes' => [
                    new L33tChangeResult(index: 5, letter: 'c', substitution: '{'),
                    new L33tChangeResult(index: 7, letter: 'o', substitution: '0'),
                ],
                'changesDisplay' => '[5] { -> c, [7] 0 -> o',
            ],
            [
                'password' => 'aac𐒰όΓοΣ𐒰',
                'pattern' => '𐒰όΓοΣ𐒰',
                'word' => 'λόγοσλ',
                'dictionary' => 'words',
                'rank' => 13,
                'beginEnds' => [3, 8],
                'changes' => [
                    new L33tChangeResult(index: 3, letter: 'λ', substitution: '𐒰'),
                    new L33tChangeResult(index: 8, letter: 'λ', substitution: '𐒰'),
                ],
                'changesDisplay' => '[3] 𐒰 -> λ, [8] 𐒰 -> λ',
            ],
        ];
    }

    /**
     * @param int[]              $beginEnds
     * @param L33tChangeResult[] $changes
     */
    #[DataProvider('provideCommonL33tSubstitutionsCases')]
    public function testCommonL33tSubstitutions(string $password, string $pattern, string $word, string $dictionary, int $rank, array $beginEnds, array $changes, string $changesDisplay): void
    {
        $this->checkMatches(
            'matches against common l33t substitutions',
            L33tMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tTable: self::$testTable))),
            'dictionary',
            [$pattern],
            [$beginEnds],
            [
                'l33t' => [true],
                'l33tExtra' => [new L33tExtraMatch(changes: $changes, changesDisplay: $changesDisplay)],
                'reversed' => [false],
                'matchedWord' => [$word],
                'rank' => [$rank],
                'dictionaryName' => [$dictionary],
            ]
        );
    }

    public function testOverlappingL33tPatterns(): void
    {
        $this->checkMatches(
            'matches against overlapping l33t patterns',
            L33tMatcher::match('@a((go{G0', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tTable: self::$testTable))),
            'dictionary',
            ['@a(', '@a((', '((go', '(go', '{G0'],
            [[0, 2], [0, 3], [2, 5], [3, 5], [6, 8]],
            [
                'l33t' => [true, true, true, true, true],
                'l33tExtra' => [
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 0, letter: 'a', substitution: '@'),
                        new L33tChangeResult(index: 2, letter: 'c', substitution: '('),
                    ], changesDisplay: '[0] @ -> a, [2] ( -> c'),
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 0, letter: 'a', substitution: '@'),
                        new L33tChangeResult(index: 2, letter: 'c', substitution: '(('),
                    ], changesDisplay: '[0] @ -> a, [2] (( -> c'),
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 2, letter: 'c', substitution: '(('),
                    ], changesDisplay: '[2] (( -> c'),
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 3, letter: 'c', substitution: '('),
                    ], changesDisplay: '[3] ( -> c'),
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 6, letter: 'c', substitution: '{'),
                        new L33tChangeResult(index: 8, letter: 'o', substitution: '0'),
                    ], changesDisplay: '[6] { -> c, [8] 0 -> o'),
                ],
                'matchedWord' => ['aac', 'aac', 'cgo', 'cgo', 'cgo'],
                'rank' => [1, 1, 1, 1, 1],
                'dictionaryName' => ['words', 'words', 'words2', 'words2', 'words2'],
            ]
        );
    }

    public function testPartialSubstitutions(): void
    {
        $this->checkMatches(
            'match when a partial substitution is needed',
            L33tMatcher::match('p4rti4l', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tTable: self::$testTable))),
            'dictionary',
            ['p4rt', 'p4rti4l'],
            [[0, 3], [0, 6]],
            [
                'l33t' => [true, true],
                'l33tExtra' => [
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 1, letter: 'a', substitution: '4'),
                    ], changesDisplay: '[1] 4 -> a'),
                    new L33tExtraMatch(changes: [
                        new L33tChangeResult(index: 1, letter: 'a', substitution: '4'),
                    ], changesDisplay: '[1] 4 -> a'),
                ],
                'matchedWord' => ['part', 'parti4l'],
                'rank' => [14, 15],
                'dictionaryName' => ['words', 'words'],
            ]
        );
    }

    public function testMultipleL33tSubstitutions(): void
    {
        $this->checkMatches(
            'match when multiple l33t substitution are needed for the same letter',
            L33tMatcher::match('p4@ssword', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tTable: self::$testTable))),
            'dictionary',
            ['p4@ssword'],
            [[0, 8]],
            [
                'l33t' => [true],
                'l33tExtra' => [new L33tExtraMatch(changes: [
                    new L33tChangeResult(index: 1, letter: 'a', substitution: '4'),
                    new L33tChangeResult(index: 2, letter: 'a', substitution: '@'),
                ], changesDisplay: '[1] 4 -> a, [2] @ -> a')],
                'matchedWord' => ['paassword'],
                'rank' => [3],
                'dictionaryName' => ['words'],
            ]
        );
    }

    public function testSubstitutionWithLevenshtein(): void
    {
        $this->checkMatches(
            'match when a full substitution is done and Levenshtein distance is used',
            L33tMatcher::match('p@((cifi{', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tTable: self::$testTable, useLevenshteinDistance: true))),
            'dictionary',
            ['p@((cifi{'],
            [[0, 8]],
            [
                'l33t' => [true],
                'l33tExtra' => [new L33tExtraMatch(changes: [
                    new L33tChangeResult(index: 1, letter: 'a', substitution: '@'),
                    new L33tChangeResult(index: 2, letter: 'c', substitution: '(('),
                    new L33tChangeResult(index: 7, letter: 'c', substitution: '{'),
                ], changesDisplay: '[1] @ -> a, [2] (( -> c, [7] { -> c')],
                'levenshteinDistance' => [1],
                'matchedWord' => ['pacific'],
                'rank' => [6],
                'dictionaryName' => ['words'],
            ]
        );
    }

    public function testPartialSubstitutionDoNotUseLevenshtein(): void
    {
        self::assertSame(
            [],
            L33tMatcher::match('4sddf0', Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tTable: self::$testTable, useLevenshteinDistance: true,levenshteinThreshold: 1))),
            'does not match when a partial substitution is done because Levenshtein distance is not used'
        );
    }

    public function testL33tMaxSubstitutions(): void
    {
        $password = '4@8({[</369&#!1/|0$5+7%2/4@8({[</369&#!1/|0$5+7%2/';
        $matches = L33tMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tMaxSubstitutions: 3)));

        $this->checkMatches(
            'respect l33t max substitutions',
            $matches,
            'dictionary',
            ['4@8({[</369&#!1/|0$5+7%2/4@8({[</369&#!1/|0$5+7%2'],
            [[0, 48]],
            [
                'l33t' => [true],
                'matchedWord' => ['aabccccvedggfiiviosstlxzvaabccccvedggfiiviosstlx2'],
                'rank' => [9],
                'dictionaryName' => ['words'],
            ]
        );
    }

    public function testL33tMaxSubstitutionsSameValue(): void
    {
        $password = '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!';
        $matches = L33tMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts, l33tMaxSubstitutions: 10)));

        self::assertEmpty($matches);
    }

    public function testResetLastConsecutiveSubstitution(): void
    {
        $password = '8T8888';
        $matches = L33tMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts)));

        $this->checkMatches(
            'reset last consecutive substitution',
            $matches,
            'dictionary',
            ['8T8888'],
            [[0, 5]],
            [
                'l33t' => [true],
                'matchedWord' => ['btbbb8'],
                'rank' => [18],
                'dictionaryName' => ['words'],
            ]
        );
    }

    public function testConsecutiveWideSubstitution(): void
    {
        $password = '|_||_||_||_|w|_|';
        $matches = L33tMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts)));

        self::assertEmpty($matches);
    }

    public function testGetPattern(): void
    {
        self::assertSame('dictionary', L33tMatcher::getPattern());
    }
}
