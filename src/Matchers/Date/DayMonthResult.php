<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Date;

final readonly class DayMonthResult
{
    public function __construct(
        public int $day,
        public int $month,
    ) {
    }
}
