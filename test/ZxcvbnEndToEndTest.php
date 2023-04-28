<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Date\DateMatch;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Matchers\Dictionary\L33tExtraMatch;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tChangeResult;
use ZxcvbnPhp\Matchers\Repeat\RepeatMatch;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatch;
use ZxcvbnPhp\Matchers\Spatial\SpatialMatch;
use ZxcvbnPhp\Result;
use ZxcvbnPhp\Result\CrackTimesDisplayResult;
use ZxcvbnPhp\Result\CrackTimesSecondsResult;
use ZxcvbnPhp\Result\FeedbackResult;
use ZxcvbnPhp\Zxcvbn;

#[CoversNothing]
final class ZxcvbnEndToEndTest extends TestCase
{
    private Zxcvbn $zxcvbn;

    protected function setUp(): void
    {
        $this->zxcvbn = new Zxcvbn();
    }

    public static function providePasswordStrengthCases(): iterable
    {
        return [
            [
                new Result(
                    password: '1q2w3e4r5t',
                    guesses: 347,
                    guessesLog10: 2.5403294747908736,
                    sequence: [
                        new DictionaryMatch(password: '1q2w3e4r5t', begin: 0, end: 9, token: '1q2w3e4r5t', matchedWord: '1q2w3e4r5t', rank: 346, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 12492, onlineNoThrottling10PerSecond: 34.7, offlineSlowHashing1e4PerSecond: 0.0347, offlineFastHashing1e10PerSecond: 3.47e-8),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '3 hours', onlineNoThrottling10PerSecond: '35 seconds', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'This is a commonly used password.', suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: '1Q2w3e4r5t',
                    guesses: 693,
                    guessesLog10: 2.8407332346118066,
                    sequence: [
                        new DictionaryMatch(password: '1Q2w3e4r5t', begin: 0, end: 9, token: '1Q2w3e4r5t', matchedWord: '1q2w3e4r5t', rank: 346, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 24948, onlineNoThrottling10PerSecond: 69.3, offlineSlowHashing1e4PerSecond: 0.0693, offlineFastHashing1e10PerSecond: 6.93e-8),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '7 hours', onlineNoThrottling10PerSecond: '1 minute', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'This is a commonly used password.', suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: '1q2w3e4r5T',
                    guesses: 693,
                    guessesLog10: 2.8407332346118066,
                    sequence: [
                        new DictionaryMatch(password: '1q2w3e4r5T', begin: 0, end: 9, token: '1q2w3e4r5T', matchedWord: '1q2w3e4r5t', rank: 346, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 24948, onlineNoThrottling10PerSecond: 69.3, offlineSlowHashing1e4PerSecond: 0.0693, offlineFastHashing1e10PerSecond: 6.93e-8),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '7 hours', onlineNoThrottling10PerSecond: '1 minute', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'This is a commonly used password.', suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'abcdefg123',
                    guesses: 15000,
                    guessesLog10: 4.176091259055681,
                    sequence: [
                        new SequenceMatch(password: 'abcdefg123', begin: 0, end: 6, token: 'abcdefg', sequenceName: 'lower', sequenceSpace: 2155, ascending: true),
                        new SequenceMatch(password: 'abcdefg123', begin: 7, end: 9, token: '123', sequenceName: 'digits', sequenceSpace: 1781, ascending: true),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 540000, onlineNoThrottling10PerSecond: 1500, offlineSlowHashing1e4PerSecond: 1.5, offlineFastHashing1e10PerSecond: 1.5e-6),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '6 days', onlineNoThrottling10PerSecond: '25 minutes', offlineSlowHashing1e4PerSecond: '2 seconds', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 1,
                    feedback: new FeedbackResult(warning: 'Common character sequences like "abc" are easy to guess.', suggestions: ['Add more words that are less common.', 'Avoid common character sequences.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'TESTERINO',
                    guesses: 2386000,
                    guessesLog10: 6.377670439334323,
                    sequence: [
                        new DictionaryMatch(password: 'TESTERINO', begin: 0, end: 5, token: 'TESTER', matchedWord: 'tester', rank: 594, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                        new BruteforceMatch(password: 'TESTERINO', begin: 6, end: 8, token: 'INO'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 85896000, onlineNoThrottling10PerSecond: 238600, offlineSlowHashing1e4PerSecond: 238.6, offlineFastHashing1e10PerSecond: 2.386e-4),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '3 years', onlineNoThrottling10PerSecond: '3 days', offlineSlowHashing1e4PerSecond: '4 minutes', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 2,
                    feedback: new FeedbackResult(warning: 'This is similar to a commonly used password.', suggestions: ['Add more words that are less common.', 'Capitalize some, but not all letters.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'aaaaaaa',
                    guesses: 85,
                    guessesLog10: 1.9294189257142929,
                    sequence: [
                        new RepeatMatch(password: 'aaaaaaa', begin: 0, end: 6, token: 'aaaaaaa', baseMatches: [new BruteforceMatch(password: 'a', begin: 0, end: 0, token: 'a')], baseGuesses: 12, repeatCount: 7, repeatedChar: 'a'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 3060, onlineNoThrottling10PerSecond: 8.5, offlineSlowHashing1e4PerSecond: 8.5e-3, offlineFastHashing1e10PerSecond: 8.5e-9),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '51 minutes', onlineNoThrottling10PerSecond: '9 seconds', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'Repeated characters like "aaa" are easy to guess.', suggestions: ['Add more words that are less common.', 'Avoid repeated words and characters.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'Daniel',
                    guesses: 113,
                    guessesLog10: 2.05307844348342,
                    sequence: [
                        new DictionaryMatch(password: 'Daniel', begin: 0, end: 5, token: 'Daniel', matchedWord: 'daniel', rank: 56, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 4068, onlineNoThrottling10PerSecond: 11.3, offlineSlowHashing1e4PerSecond: 0.0113, offlineFastHashing1e10PerSecond: 1.13e-8),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '1 hour', onlineNoThrottling10PerSecond: '11 seconds', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'This is a frequently used password.', suggestions: ['Add more words that are less common.', 'Capitalize more than the first letter.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: '1234qwer',
                    guesses: 105,
                    guessesLog10: 2.0211892990699383,
                    sequence: [
                        new DictionaryMatch(password: '1234qwer', begin: 0, end: 7, token: '1234qwer', matchedWord: '1234qwer', rank: 104, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 3780, onlineNoThrottling10PerSecond: 10.5, offlineSlowHashing1e4PerSecond: 0.0105, offlineFastHashing1e10PerSecond: 1.05e-8),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '1 hour', onlineNoThrottling10PerSecond: '11 seconds', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'This is a commonly used password.', suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: '1234qwe',
                    guesses: 2845,
                    guessesLog10: 3.45408227073109,
                    sequence: [
                        new DictionaryMatch(password: '1234qwe', begin: 0, end: 6, token: '1234qwe', matchedWord: '1234qwe', rank: 2844, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 102420, onlineNoThrottling10PerSecond: 284.5, offlineSlowHashing1e4PerSecond: 0.2845, offlineFastHashing1e10PerSecond: 2.845e-7),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '1 day', onlineNoThrottling10PerSecond: '5 minutes', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 1,
                    feedback: new FeedbackResult(warning: 'This is a commonly used password.', suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: '1234qwert',
                    guesses: 12288,
                    guessesLog10: 4.089481202687437,
                    sequence: [
                        new DictionaryMatch(password: '1234qwert', begin: 0, end: 7, token: '1234qwer', matchedWord: '1234qwer', rank: 104, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                        new BruteforceMatch(password: '1234qwert', begin: 8, end: 8, token: 't'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 442368, onlineNoThrottling10PerSecond: 1228.8, offlineSlowHashing1e4PerSecond: 1.2288, offlineFastHashing1e10PerSecond: 1.2288e-6),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '5 days', onlineNoThrottling10PerSecond: '20 minutes', offlineSlowHashing1e4PerSecond: '1 second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 1,
                    feedback: new FeedbackResult(warning: 'This is similar to a commonly used password.', suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'password',
                    guesses: 3,
                    guessesLog10: 0.47712125471966244,
                    sequence: [
                        new DictionaryMatch(password: 'password', begin: 0, end: 7, token: 'password', matchedWord: 'password', rank: 2, dictionaryName: 'common-passwords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 108, onlineNoThrottling10PerSecond: 0.3, offlineSlowHashing1e4PerSecond: 0.0003, offlineFastHashing1e10PerSecond: 3.0e-10),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '2 minutes', onlineNoThrottling10PerSecond: 'less than a second', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'This is a heavily used password.', suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: '2010abc',
                    guesses: 15000,
                    guessesLog10: 4.176091259055681,
                    sequence: [
                        new DateMatch(password: '2010abc', begin: 0, end: 3, token: '2010', day: -1, month: -1, year: 2010, separator: ''),
                        new SequenceMatch(password: '2010abc', begin: 4, end: 6, token: 'abc', sequenceName: 'lower', sequenceSpace: 2155, ascending: true),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 540000, onlineNoThrottling10PerSecond: 1500, offlineSlowHashing1e4PerSecond: 1.5, offlineFastHashing1e10PerSecond: 1.5e-6),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '6 days', onlineNoThrottling10PerSecond: '25 minutes', offlineSlowHashing1e4PerSecond: '2 seconds', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 1,
                    feedback: new FeedbackResult(warning: 'Recent years are easy to guess.', suggestions: ['Add more words that are less common.', 'Avoid recent years.', 'Avoid years that are associated with you.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'abcabcabcabc',
                    guesses: 53,
                    guessesLog10: 1.7242758696007892,
                    sequence: [
                        new RepeatMatch(password: 'abcabcabcabc', begin: 0, end: 11, token: 'abcabcabcabc', baseMatches: [new SequenceMatch(password: 'abc', begin: 0, end: 2, token: 'abc', sequenceName: 'lower', sequenceSpace: 2155, ascending: true)], baseGuesses: 13, repeatCount: 4, repeatedChar: 'abc'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 1908, onlineNoThrottling10PerSecond: 5.3, offlineSlowHashing1e4PerSecond: 0.0053, offlineFastHashing1e10PerSecond: 5.3e-9),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '32 minutes', onlineNoThrottling10PerSecond: '5 seconds', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'Repeated character patterns like "abcabcabc" are easy to guess.', suggestions: ['Add more words that are less common.', 'Avoid repeated words and characters.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'qwer',
                    guesses: 1297.0000000000002,
                    guessesLog10: 3.11293997608408,
                    sequence: [
                        new SpatialMatch(password: 'qwer', begin: 0, end: 3, token: 'qwer', graph: 'qwerty', shiftedCount: 0, turns: 1),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 46692.00000000001, onlineNoThrottling10PerSecond: 129.70000000000002, offlineSlowHashing1e4PerSecond: 0.1297, offlineFastHashing1e10PerSecond: 1.2970000000000003e-7),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '13 hours', onlineNoThrottling10PerSecond: '2 minutes', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 1,
                    feedback: new FeedbackResult(warning: 'Straight rows of keys on your keyboard are easy to guess.', suggestions: ['Add more words that are less common.', 'Use longer keyboard patterns and change typing direction multiple times.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'P4$$w0rd',
                    guesses: 257,
                    guessesLog10: 2.4099331233312946,
                    sequence: [
                        new DictionaryMatch(password: 'P4$$w0rd', begin: 0, end: 7, token: 'P4$$w0rd', matchedWord: 'password', rank: 2, dictionaryName: 'common-passwords', reversed: false, l33t: true, levenshteinDistance: -1, l33tExtra: new L33tExtraMatch(
                            changes: [
                                new L33tChangeResult(index: 1, letter: 'a', substitution: '4'),
                                new L33tChangeResult(index: 2, letter: 's', substitution: '$'),
                                new L33tChangeResult(index: 3, letter: 's', substitution: '$'),
                                new L33tChangeResult(index: 5, letter: 'o', substitution: '0'),
                            ],
                            changesDisplay: '[1] 4 -> a, [2] $ -> s, [3] $ -> s, [5] 0 -> o')
                        ),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 9252, onlineNoThrottling10PerSecond: 25.7, offlineSlowHashing1e4PerSecond: 0.0257, offlineFastHashing1e10PerSecond: 2.57e-8),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '3 hours', onlineNoThrottling10PerSecond: '26 seconds', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: 'This is similar to a commonly used password.', suggestions: ['Add more words that are less common.', 'Capitalize more than the first letter.', "Avoid predictable letter substitutions like '@' for 'a'."]),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'aA!1',
                    guesses: 10001,
                    guessesLog10: 4.000043427276863,
                    sequence: [
                        new BruteforceMatch(password: 'aA!1', begin: 0, end: 3, token: 'aA!1'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 360036, onlineNoThrottling10PerSecond: 1000.1, offlineSlowHashing1e4PerSecond: 1.0001, offlineFastHashing1e10PerSecond: 1.0001e-6),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '4 days', onlineNoThrottling10PerSecond: '17 minutes', offlineSlowHashing1e4PerSecond: '1 second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 1,
                    feedback: new FeedbackResult(warning: null, suggestions: ['Add more words that are less common.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'dgo9dsghasdoghi8/!&IT%§(ihsdhf8o7o',
                    guesses: 2.34e33,
                    guessesLog10: 33.36921585741014,
                    sequence: [
                        new BruteforceMatch(password: 'dgo9dsghasdoghi8/!&IT%§(ihsdhf8o7o', begin: 0, end: 6, token: 'dgo9dsg'),
                        new DictionaryMatch(password: 'dgo9dsghasdoghi8/!&IT%§(ihsdhf8o7o', begin: 7, end: 9, token: 'has', matchedWord: 'has', rank: 29, dictionaryName: 'en-wikipedia', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                        new DictionaryMatch(password: 'dgo9dsghasdoghi8/!&IT%§(ihsdhf8o7o', begin: 10, end: 11, token: 'do', matchedWord: 'do', rank: 14, dictionaryName: 'en-commonWords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                        new SequenceMatch(password: 'dgo9dsghasdoghi8/!&IT%§(ihsdhf8o7o', begin: 12, end: 14, token: 'ghi', sequenceName: 'lower', sequenceSpace: 2155, ascending: true),
                        new BruteforceMatch(password: 'dgo9dsghasdoghi8/!&IT%§(ihsdhf8o7o', begin: 15, end: 33, token: '8/!&IT%§(ihsdhf8o7o'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 8.424e34, onlineNoThrottling10PerSecond: 2.34e32, offlineSlowHashing1e4PerSecond: 2.34e29, offlineFastHashing1e10PerSecond: 2.34e23),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: 'centuries', onlineNoThrottling10PerSecond: 'centuries', offlineSlowHashing1e4PerSecond: 'centuries', offlineFastHashing1e10PerSecond: 'centuries'),
                    score: 4,
                    feedback: new FeedbackResult(warning: null, suggestions: []),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'AZERYT',
                    guesses: 538000,
                    guessesLog10: 5.730782275666389,
                    sequence: [
                        new SpatialMatch(password: 'AZERYT', begin: 0, end: 3, token: 'AZER', graph: 'azerty', shiftedCount: 4, turns: 1),
                        new BruteforceMatch(password: 'AZERYT', begin: 4, end: 5, token: 'YT'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 19368000, onlineNoThrottling10PerSecond: 53800, offlineSlowHashing1e4PerSecond: 53.8, offlineFastHashing1e10PerSecond: 5.38e-5),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '7 months', onlineNoThrottling10PerSecond: '15 hours', offlineSlowHashing1e4PerSecond: '54 seconds', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 1,
                    feedback: new FeedbackResult(warning: 'Straight rows of keys on your keyboard are easy to guess.', suggestions: ['Add more words that are less common.', 'Use longer keyboard patterns and change typing direction multiple times.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'zxcftzuio',
                    guesses: 12790054.375,
                    guessesLog10: 7.106872390820503,
                    sequence: [
                        new BruteforceMatch(password: 'zxcftzuio', begin: 0, end: 0, token: 'z'),
                        new SpatialMatch(password: 'zxcftzuio', begin: 1, end: 8, token: 'xcftzuio', graph: 'qwertz', shiftedCount: 0, turns: 3),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 460441957.5, onlineNoThrottling10PerSecond: 1279005.4375, offlineSlowHashing1e4PerSecond: 1279.0054375, offlineFastHashing1e10PerSecond: 0.0012790054375),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '14 years', onlineNoThrottling10PerSecond: '15 days', offlineSlowHashing1e4PerSecond: '21 minutes', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 2,
                    feedback: new FeedbackResult(warning: 'Short keyboard patterns are easy to guess.', suggestions: ['Add more words that are less common.', 'Use longer keyboard patterns and change typing direction multiple times.']),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'buy by beer',
                    guesses: 1301200000,
                    guessesLog10: 9.114344054609816,
                    sequence: [
                        new BruteforceMatch(password: 'buy by beer', begin: 0, end: 1, token: 'bu'),
                        new RepeatMatch(password: 'buy by beer', begin: 2, end: 7, token: 'y by b', baseMatches: [new BruteforceMatch(password: 'y b', begin: 0, end: 2, token: 'y b')], baseGuesses: 1001, repeatCount: 2, repeatedChar: 'y b'),
                        new BruteforceMatch(password: 'buy by beer', begin: 8, end: 10, token: 'eer'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 46843200000, onlineNoThrottling10PerSecond: 130120000, offlineSlowHashing1e4PerSecond: 130120, offlineFastHashing1e10PerSecond: 0.13012),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: 'centuries', onlineNoThrottling10PerSecond: '4 years', offlineSlowHashing1e4PerSecond: '2 days', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 3,
                    feedback: new FeedbackResult(),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'horse stable battery',
                    guesses: 1119124900000000,
                    guessesLog10: 15.048878558694355,
                    sequence: [
                        new DictionaryMatch(password: 'horse stable battery', begin: 0, end: 4, token: 'horse', matchedWord: 'horse', rank: 654, dictionaryName: 'en-commonWords', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                        new BruteforceMatch(password: 'horse stable battery', begin: 5, end: 12, token: ' stable '),
                        new DictionaryMatch(password: 'horse stable battery', begin: 13, end: 19, token: 'battery', matchedWord: 'battery', rank: 2852, dictionaryName: 'en-wikipedia', reversed: false, l33t: false, levenshteinDistance: -1, l33tExtra: null),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 40288496400000000, onlineNoThrottling10PerSecond: 111912490000000, offlineSlowHashing1e4PerSecond: 111912490000, offlineFastHashing1e10PerSecond: 111912.49),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: 'centuries', onlineNoThrottling10PerSecond: 'centuries', offlineSlowHashing1e4PerSecond: 'centuries', offlineFastHashing1e10PerSecond: '1 day'),
                    score: 4,
                    feedback: new FeedbackResult(),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: 'a65a54cf-eadb-4f7c-893c-9d4a6f81f8c2',
                    guesses: 1.0e36,
                    guessesLog10: 36,
                    sequence: [
                        new BruteforceMatch(password: 'a65a54cf-eadb-4f7c-893c-9d4a6f81f8c2', begin: 0, end: 35, token: 'a65a54cf-eadb-4f7c-893c-9d4a6f81f8c2'),
                    ],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 3.6000000000000004e37, onlineNoThrottling10PerSecond: 1.0e35, offlineSlowHashing1e4PerSecond: 1.0e32, offlineFastHashing1e10PerSecond: 1.0e26),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: 'centuries', onlineNoThrottling10PerSecond: 'centuries', offlineSlowHashing1e4PerSecond: 'centuries', offlineFastHashing1e10PerSecond: 'centuries'),
                    score: 4,
                    feedback: new FeedbackResult(),
                    calcTime: -1,
                ),
            ],
            [
                new Result(
                    password: '',
                    guesses: 1,
                    guessesLog10: 0,
                    sequence: [],
                    crackTimesSeconds: new CrackTimesSecondsResult(onlineThrottling100PerHour: 36, onlineNoThrottling10PerSecond: 0.1, offlineSlowHashing1e4PerSecond: 0.0001, offlineFastHashing1e10PerSecond: 1.0e-10),
                    crackTimesDisplay: new CrackTimesDisplayResult(onlineThrottling100PerHour: '36 seconds', onlineNoThrottling10PerSecond: 'less than a second', offlineSlowHashing1e4PerSecond: 'less than a second', offlineFastHashing1e10PerSecond: 'less than a second'),
                    score: 0,
                    feedback: new FeedbackResult(warning: null, suggestions: ['Use multiple words, but avoid common phrases.', 'You can create strong passwords without using symbols, numbers, or uppercase letters.']),
                    calcTime: -1,
                ),
            ],
        ];
    }

    #[DataProvider('providePasswordStrengthCases')]
    public function testPasswordStrength(Result $result): void
    {
        $zxcvbn = new Zxcvbn(new Config(calcTimeEnabled: false));

        self::assertEquals($result, $zxcvbn->passwordStrength($result->password));
    }

    #[Group('attack')]
    public function testL33tAttack(): void
    {
        $password = '4@8({[</369&#!1/|0$5+7%2/4@8({[</369&#!1/|0$5+7%2/';
        $result = $this->zxcvbn->passwordStrength($password);

        self::assertLessThan(2, $result->calcTime);
    }

    #[Group('attack')]
    public function testSameValueL33tAttack(): void
    {
        $password = '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!';
        $result = $this->zxcvbn->passwordStrength($password);

        self::assertLessThan(2, $result->calcTime);
    }

    #[Group('attack')]
    public function testSequenceAttack(): void
    {
        $password = str_repeat("\x00", 200);
        $result = $this->zxcvbn->passwordStrength($password);

        self::assertLessThan(2, $result->calcTime);
    }
}
