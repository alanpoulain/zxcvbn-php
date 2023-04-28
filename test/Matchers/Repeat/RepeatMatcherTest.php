<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Repeat;

use PHPUnit\Framework\Attributes\CoversClass;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Repeat\RepeatMatch;
use ZxcvbnPhp\Matchers\Repeat\RepeatMatcher;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatch;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(RepeatMatcher::class)]
#[CoversClass(RepeatMatch::class)]
final class RepeatMatcherTest extends AbstractMatchTestCase
{
    public function testEmpty(): void
    {
        foreach (['', '#'] as $password) {
            self::assertEmpty(
                RepeatMatcher::match($password, Configurator::getOptions(new Config())),
                "doesn't match length-".\strlen($password).' repeat patterns'
            );
        }
    }

    public function testSingleCharacterEmbeddedRepeats(): void
    {
        $prefixes = ['@', 'y4@'];
        $suffixes = ['u', 'u%7'];
        $pattern = '&&&&&';

        foreach ($this->generatePasswords($pattern, $prefixes, $suffixes) as [$password, $begin, $end]) {
            $this->checkMatches(
                'matches embedded repeat patterns',
                RepeatMatcher::match($password, Configurator::getOptions(new Config())),
                'repeat',
                [$pattern],
                [[$begin, $end]],
                [
                    'repeatedChar' => ['&'],
                    'repeatCount' => [5],
                ]
            );
        }
    }

    public function testSingleCharacterRepeats(): void
    {
        foreach ([3, 12] as $length) {
            foreach (['a', 'Z', '4', '&'] as $char) {
                $pattern = str_repeat($char, $length);

                $this->checkMatches(
                    "matches repeats with base character '{$char}'",
                    RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
                    'repeat',
                    [$pattern],
                    [[0, \strlen($pattern) - 1]],
                    [
                        'repeatedChar' => [$char],
                        'repeatCount' => [$length],
                    ]
                );
            }
        }
    }

    public function testAdjacentRepeats(): void
    {
        $password = 'BBB1111aaaaa@@@@@@';
        $patterns = ['BBB', '1111', 'aaaaa', '@@@@@@'];

        $this->checkMatches(
            'matches multiple adjacent repeats',
            RepeatMatcher::match($password, Configurator::getOptions(new Config())),
            'repeat',
            $patterns,
            [[0, 2], [3, 6], [7, 11], [12, 17]],
            [
                'repeatedChar' => ['B', '1', 'a', '@'],
                'repeatCount' => [3, 4, 5, 6],
            ]
        );
    }

    public function testMultipleNonAdjacentRepeats(): void
    {
        $password = '2818BBBbzsdf1111@*&@!aaaaaEUDA@@@@@@1729';
        $patterns = ['BBB', '1111', 'aaaaa', '@@@@@@'];

        $this->checkMatches(
            'matches multiple repeats with non-repeats in-between',
            RepeatMatcher::match($password, Configurator::getOptions(new Config())),
            'repeat',
            $patterns,
            [[4, 6], [12, 15], [21, 25], [30, 35]],
            [
                'repeatedChar' => ['B', '1', 'a', '@'],
                'repeatCount' => [3, 4, 5, 6],
            ]
        );
    }

    public function testMultiCharacterRepeats(): void
    {
        $pattern = 'abab';

        $this->checkMatches(
            'matches multi-character repeat pattern',
            RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
            'repeat',
            [$pattern],
            [[0, \strlen($pattern) - 1]],
            [
                'repeatedChar' => ['ab'],
                'repeatCount' => [2],
            ]
        );
    }

    public function testGreedyMultiCharacterRepeats(): void
    {
        $pattern = 'aabaab';

        $this->checkMatches(
            'matches aabaab as a repeat instead of the aa prefix',
            RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
            'repeat',
            [$pattern],
            [[0, \strlen($pattern) - 1]],
            [
                'repeatedChar' => ['aab'],
                'repeatCount' => [2],
            ]
        );
    }

    public function testFrequentlyRepeatedMultiCharacterRepeats(): void
    {
        $pattern = 'abababab';

        $this->checkMatches(
            'identifies ab as repeat string, even though abab is also repeated',
            RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
            'repeat',
            [$pattern],
            [[0, \strlen($pattern) - 1]],
            [
                'repeatedChar' => ['ab'],
                'repeatCount' => [4],
            ]
        );
    }

    public function testMultibyteRepeat(): void
    {
        $pattern = '🙂🙂🙂';

        $this->checkMatches(
            'detects repeated multibyte characters',
            RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
            'repeat',
            [$pattern],
            [[0, 2]],
            [
                'repeatedChar' => ['🙂'],
                'repeatCount' => [3],
            ]
        );
    }

    public function testRepeatAfterMultibyteCharacters(): void
    {
        $pattern = 'niÃ±abella';

        $this->checkMatches(
            'detects repeat with correct offset after multibyte characters',
            RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
            'repeat',
            ['ll'],
            [[7, 8]],
            [
                'repeatedChar' => ['l'],
                'repeatCount' => [2],
            ]
        );
    }

    public function testDuplicateRepeatsInPassword(): void
    {
        $pattern = 'scoobydoo';

        $this->checkMatches(
            'duplicate repeats in the password are identified correctly',
            RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
            'repeat',
            ['oo', 'oo'],
            [[2, 3], [7, 8]],
            [
                'repeatedChar' => ['o', 'o'],
                'repeatCount' => [2, 2],
            ]
        );
    }

    public function testBaseGuesses(): void
    {
        $pattern = 'abcabc';

        $this->checkMatches(
            'calculates the correct number of guesses for the base token',
            RepeatMatcher::match($pattern, Configurator::getOptions(new Config())),
            'repeat',
            [$pattern],
            [[0, \strlen($pattern) - 1]],
            [
                'repeatedChar' => ['abc'],
                'repeatCount' => [2],
                'baseGuesses' => [13.0],
            ]
        );
    }

    public function testBaseMatches(): void
    {
        $pattern = 'abcabc';
        $match = RepeatMatcher::match($pattern, Configurator::getOptions(new Config()))[0];
        $baseMatches = $match->baseMatches();

        self::assertCount(1, $baseMatches);
        self::assertInstanceOf(SequenceMatch::class, $baseMatches[0]);
    }

    public function testBaseMatchesRecursive(): void
    {
        $pattern = 'mqmqmqltltltmqmqmqltltlt';
        $match = RepeatMatcher::match($pattern, Configurator::getOptions(new Config()))[0];
        self::assertSame('mqmqmqltltlt', $match->repeatedChar());

        $baseMatches = $match->baseMatches();
        self::assertInstanceOf(RepeatMatch::class, $baseMatches[0]);
        self::assertSame('mq', $baseMatches[0]->repeatedChar());

        self::assertInstanceOf(RepeatMatch::class, $baseMatches[1]);
        self::assertSame('lt', $baseMatches[1]->repeatedChar());
    }

    public function testGetPattern(): void
    {
        self::assertSame('repeat', RepeatMatcher::getPattern());
    }
}
