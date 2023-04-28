<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Result;

final readonly class AttackTimesResult
{
    public function __construct(
        public CrackTimesSecondsResult $crackTimesSeconds,
        public CrackTimesDisplayResult $crackTimesDisplay,
        /** @var int<0, 4> */
        public int $score,
    ) {
    }
}
