<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Spatial;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Spatial\SpatialFeedback;
use ZxcvbnPhp\Matchers\Spatial\SpatialMatch;

#[CoversClass(SpatialFeedback::class)]
final class SpatialFeedbackTest extends TestCase
{
    public function testFeedbackStraightLine(): void
    {
        $token = 'dfghjk';
        $match = new SpatialMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            graph: 'qwerty',
            shiftedCount: 0,
            turns: 1
        );
        $feedback = SpatialFeedback::getFeedback($match, Configurator::getOptions(new Config()));

        self::assertSame(
            'warnings.straightRow',
            $feedback->warning,
            'spatial match in straight line gives correct warning'
        );
        self::assertContains(
            'suggestions.longerKeyboardPattern',
            $feedback->suggestions,
            'spatial match in straight line gives correct suggestion'
        );
    }

    public function testFeedbackWithTurns(): void
    {
        $token = 'xcvgy789';
        $match = new SpatialMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            graph: 'qwerty',
            shiftedCount: 0,
            turns: 3
        );
        $feedback = SpatialFeedback::getFeedback($match, Configurator::getOptions(new Config()));

        self::assertSame(
            'warnings.keyPattern',
            $feedback->warning,
            'spatial match with turns gives correct warning'
        );
        self::assertContains(
            'suggestions.longerKeyboardPattern',
            $feedback->suggestions,
            'spatial match with turns gives correct suggestion'
        );
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Spatial\SpatialMatch');

        SpatialFeedback::getFeedback(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('spatial', SpatialFeedback::getPattern());
    }
}
