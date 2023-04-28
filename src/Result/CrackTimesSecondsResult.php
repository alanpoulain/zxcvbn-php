<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Result;

final class CrackTimesSecondsResult
{
    public function __construct(
        public float $onlineThrottling100PerHour,
        public float $onlineNoThrottling10PerSecond,
        public float $offlineSlowHashing1e4PerSecond,
        public float $offlineFastHashing1e10PerSecond,
    ) {
    }
}
