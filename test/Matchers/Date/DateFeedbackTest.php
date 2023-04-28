<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Date;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Date\DateFeedback;
use ZxcvbnPhp\Matchers\Date\DateMatch;

#[CoversClass(DateFeedback::class)]
final class DateFeedbackTest extends TestCase
{
    public function testFeedback(): void
    {
        $token = '26/01/1990';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: 26,
            month: 1,
            year: 1990,
            separator: '/'
        );
        $feedback = DateFeedback::getFeedback($match, Configurator::getOptions(new Config()));

        self::assertSame(
            'warnings.dates',
            $feedback->warning,
            'date match gives correct warning'
        );
        self::assertContains(
            'suggestions.dates',
            $feedback->suggestions,
            'date match gives correct suggestion'
        );
    }

    public function testFeedbackRecentYears(): void
    {
        $token = '2024';
        $match = new DateMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            day: -1,
            month: -1,
            year: 2024,
            separator: ''
        );
        $feedback = DateFeedback::getFeedback($match, Configurator::getOptions(new Config()));

        self::assertSame(
            'warnings.recentYears',
            $feedback->warning,
            'year match gives correct warning'
        );
        self::assertContains(
            'suggestions.recentYears',
            $feedback->suggestions,
            'year match gives correct suggestion #1'
        );
        self::assertContains(
            'suggestions.associatedYears',
            $feedback->suggestions,
            'year match gives correct suggestion #2'
        );
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Date\DateMatch');

        DateFeedback::getFeedback(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('date', DateFeedback::getPattern());
    }
}
