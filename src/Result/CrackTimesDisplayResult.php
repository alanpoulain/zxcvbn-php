<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Result;

final class CrackTimesDisplayResult
{
    public function __construct(
        public string $onlineThrottling100PerHour,
        public string $onlineNoThrottling10PerSecond,
        public string $offlineSlowHashing1e4PerSecond,
        public string $offlineFastHashing1e10PerSecond,
    ) {
    }
}
