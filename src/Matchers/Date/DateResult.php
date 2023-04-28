<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Date;

final readonly class DateResult
{
    public function __construct(
        public int $day,
        public int $month,
        public int $year,
    ) {
    }
}
