<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Result\AttackTimesResult;
use ZxcvbnPhp\Result\CrackTimesDisplayResult;
use ZxcvbnPhp\Result\CrackTimesSecondsResult;
use ZxcvbnPhp\Translation\Translator;

/**
 * Gives some user guidance based on the strength of a password.
 */
final class TimeEstimator
{
    private const SECOND = 1;
    private const MINUTE = self::SECOND * 60;
    private const HOUR = self::MINUTE * 60;
    private const DAY = self::HOUR * 24;
    private const MONTH = self::DAY * 31;
    private const YEAR = self::MONTH * 12;
    private const CENTURY = self::YEAR * 100;

    private array $times = [
        'ltSecond' => 0,
        'seconds' => self::SECOND,
        'minutes' => self::MINUTE,
        'hours' => self::HOUR,
        'days' => self::DAY,
        'months' => self::MONTH,
        'years' => self::YEAR,
        'centuries' => self::CENTURY,
    ];

    private Options $options;

    public function __construct(?Options $options = null)
    {
        $this->options = $options ?? Configurator::getOptions(new Config());
    }

    public function estimateAttackTimes(float $guesses): AttackTimesResult
    {
        $crackTimesSeconds = new CrackTimesSecondsResult(
            onlineThrottling100PerHour: $guesses / (100 / 3600),
            onlineNoThrottling10PerSecond: $guesses / 10,
            offlineSlowHashing1e4PerSecond: $guesses / 1e4,
            offlineFastHashing1e10PerSecond: $guesses / 1e10,
        );

        $crackTimesDisplay = new CrackTimesDisplayResult(
            onlineThrottling100PerHour: $this->displayTime($crackTimesSeconds->onlineThrottling100PerHour),
            onlineNoThrottling10PerSecond: $this->displayTime($crackTimesSeconds->onlineNoThrottling10PerSecond),
            offlineSlowHashing1e4PerSecond: $this->displayTime($crackTimesSeconds->offlineSlowHashing1e4PerSecond),
            offlineFastHashing1e10PerSecond: $this->displayTime($crackTimesSeconds->offlineFastHashing1e10PerSecond),
        );

        return new AttackTimesResult(
            crackTimesSeconds: $crackTimesSeconds,
            crackTimesDisplay: $crackTimesDisplay,
            score: $this->guessesToScore($guesses),
        );
    }

    private function guessesToScore(float $guesses): int
    {
        $DELTA = 5;

        if ($guesses < 1e3 + $DELTA) {
            // Risky password: "too guessable".
            return 0;
        }

        if ($guesses < 1e6 + $DELTA) {
            // Modest protection from throttled online attacks: "very guessable".
            return 1;
        }

        if ($guesses < 1e8 + $DELTA) {
            // Modest protection from unthrottled online attacks: "somewhat guessable".
            return 2;
        }

        if ($guesses < 1e10 + $DELTA) {
            // Modest protection from offline attacks: "safely unguessable".
            // Assuming a salted, slow hash function like bcrypt, scrypt, PBKDF2, argon, etc.
            return 3;
        }

        // Strong protection from offline attacks under same scenario: "very unguessable".
        return 4;
    }

    private function displayTime(float $seconds): string
    {
        $timeLabels = array_keys($this->times);
        $foundIndex = \count($timeLabels);
        foreach ($timeLabels as $index => $timeLabel) {
            if ($seconds < $this->times[$timeLabel]) {
                $foundIndex = $index;
                break;
            }
        }

        $key = $timeLabels[$foundIndex - 1];
        $base = $foundIndex > 1 ? (int) round($seconds / $this->times[$key]) : 1;

        return $this->translate($key, $base);
    }

    private function translate(string $key, int $base): string
    {
        return Translator::getTranslator($this->options)->trans(sprintf('timeEstimation.%s', $key), ['base' => $base]);
    }
}
