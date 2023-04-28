<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Repeat;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Repeat\RepeatFeedback;
use ZxcvbnPhp\Matchers\Repeat\RepeatMatch;

#[CoversClass(RepeatFeedback::class)]
final class RepeatFeedbackTest extends TestCase
{
    public function testFeedbackSingleCharacterRepeat(): void
    {
        $token = 'bbbbbb';
        $match = new RepeatMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            baseMatches: [],
            baseGuesses: 1,
            repeatCount: 6,
            repeatedChar: 'b'
        );
        $feedback = RepeatFeedback::getFeedback($match, Configurator::getOptions(new Config()));

        self::assertSame(
            'warnings.simpleRepeat',
            $feedback->warning,
            'one repeated character gives correct warning'
        );
        self::assertContains(
            'suggestions.repeated',
            $feedback->suggestions,
            'one repeated character gives correct suggestion'
        );
    }

    public function testFeedbackMultipleCharacterRepeat(): void
    {
        $token = 'bababa';
        $match = new RepeatMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            baseMatches: [],
            baseGuesses: 1,
            repeatCount: 3,
            repeatedChar: 'ba'
        );
        $feedback = RepeatFeedback::getFeedback($match, Configurator::getOptions(new Config()));

        self::assertSame(
            'warnings.extendedRepeat',
            $feedback->warning,
            'multiple repeated characters gives correct warning'
        );
        self::assertContains(
            'suggestions.repeated',
            $feedback->suggestions,
            'multiple repeated characters gives correct suggestion'
        );
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Repeat\RepeatMatch');

        RepeatFeedback::getFeedback(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('repeat', RepeatFeedback::getPattern());
    }
}
