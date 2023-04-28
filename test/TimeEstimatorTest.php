<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Result\AttackTimesResult;
use ZxcvbnPhp\Result\CrackTimesDisplayResult;
use ZxcvbnPhp\Result\CrackTimesSecondsResult;
use ZxcvbnPhp\TimeEstimator;

#[CoversClass(TimeEstimator::class)]
#[CoversClass(AttackTimesResult::class)]
#[CoversClass(CrackTimesDisplayResult::class)]
#[CoversClass(CrackTimesSecondsResult::class)]
final class TimeEstimatorTest extends TestCase
{
    private TimeEstimator $timeEstimator;

    protected function setUp(): void
    {
        $this->timeEstimator = new TimeEstimator();
    }

    public function testTime100PerHour(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(100)->crackTimesDisplay->onlineThrottling100PerHour;

        self::assertSame('1 hour', $actual, '100 guesses / 100 per hour = 1 hour');
    }

    public function testTime10PerSecond(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(10)->crackTimesDisplay->onlineNoThrottling10PerSecond;

        self::assertSame('1 second', $actual, '10 guesses / 10 per second = 1 second');
    }

    public function testTime1e4PerSecond(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(1e5)->crackTimesDisplay->offlineSlowHashing1e4PerSecond;

        self::assertSame('10 seconds', $actual, '1e5 guesses / 1e4 per second = 10 seconds');
    }

    public function testTime1e10PerSecond(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(2e11)->crackTimesDisplay->offlineFastHashing1e10PerSecond;

        self::assertSame('20 seconds', $actual, '2e11 guesses / 1e10 per second = 20 seconds');
    }

    public function testTimeLessThanASecond(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(1)->crackTimesDisplay->offlineFastHashing1e10PerSecond;

        self::assertSame('less than a second', $actual, 'less than a second');
    }

    public function testTimeCenturies(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(1e10)->crackTimesDisplay->onlineThrottling100PerHour;

        self::assertSame('centuries', $actual, 'centuries');
    }

    public function testTimeRounding(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(1500)->crackTimesDisplay->onlineNoThrottling10PerSecond;

        self::assertSame('3 minutes', $actual, '1500 guesses / 10 per second = 3 minutes and not 2.5 minutes');
    }

    public function testPlurals(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(12)->crackTimesDisplay->onlineNoThrottling10PerSecond;
        self::assertSame('1 second', $actual, 'no plural if unit value is 1');

        $actual = $this->timeEstimator->estimateAttackTimes(22)->crackTimesDisplay->onlineNoThrottling10PerSecond;
        self::assertSame('2 seconds', $actual, 'plural if unit value is more than 1');
    }

    public function testNotTranslatedTime(): void
    {
        $timeEstimator = new TimeEstimator(Configurator::getOptions(new Config(translationEnabled: false)));
        $actual = $timeEstimator->estimateAttackTimes(1)->crackTimesDisplay->offlineFastHashing1e10PerSecond;

        self::assertSame('timeEstimation.ltSecond', $actual, 'time not translated');
    }

    public function testSpanishTranslatedFeedback(): void
    {
        $timeEstimator = new TimeEstimator(Configurator::getOptions(new Config(translationLocale: 'es-es')));
        $actual = $timeEstimator->estimateAttackTimes(1)->crackTimesDisplay->offlineFastHashing1e10PerSecond;

        self::assertSame('menos de un segundo', $actual, 'time translated in Spanish');
    }

    public static function provideTimeUnitsCases(): iterable
    {
        return [
            [1e2, '10 seconds'],
            [1e3, '2 minutes'],
            [1e5, '3 hours'],
            [1e7, '12 days'],
            [1e8, '4 months'],
            [1e9, '3 years'],
        ];
    }

    #[DataProvider('provideTimeUnitsCases')]
    public function testTimeUnits(float $guesses, string $displayText): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes($guesses)->crackTimesDisplay->onlineNoThrottling10PerSecond;

        self::assertSame($displayText, $actual);
    }

    public function testDifferentSpeeds(): void
    {
        $results = $this->timeEstimator->estimateAttackTimes(1e10)->crackTimesSeconds;

        self::assertSame(1e10 / 1e10, $results->offlineFastHashing1e10PerSecond);
        self::assertSame(1e10 / 1e4, $results->offlineSlowHashing1e4PerSecond);
        self::assertSame(1e10 / 10, $results->onlineNoThrottling10PerSecond);
        self::assertSame(1e10 / (100 / 3600), $results->onlineThrottling100PerHour);
    }

    public function testSpeedLessThanOne(): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes(100)->crackTimesSeconds->offlineSlowHashing1e4PerSecond;

        self::assertSame(0.01, $actual, 'decimal speed when less than one second');
    }

    public static function provideScoresCases(): iterable
    {
        return [
            [1e2, 0],
            [1e4, 1],
            [1e7, 2],
            [1e9, 3],
            [1e11, 4],
        ];
    }

    #[DataProvider('provideScoresCases')]
    public function testScores(float $guesses, int $expectedScore): void
    {
        $actual = $this->timeEstimator->estimateAttackTimes($guesses)->score;

        self::assertSame($expectedScore, $actual, 'correct score');
    }

    public function testScoreDelta(): void
    {
        $score = $this->timeEstimator->estimateAttackTimes(1000)->score;
        self::assertSame(0, $score, 'guesses at threshold gets lower score');

        $score = $this->timeEstimator->estimateAttackTimes(1003)->score;
        self::assertSame(0, $score, 'guesses just above threshold gets lower score');

        $score = $this->timeEstimator->estimateAttackTimes(1010)->score;
        self::assertSame(1, $score, 'guesses above delta gets higher score');
    }
}
