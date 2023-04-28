<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Date;

use ZxcvbnPhp\Matchers\AbstractMatch;

final class DateMatch extends AbstractMatch
{
    public const PATTERN = 'date';

    public function __construct(
        #[\SensitiveParameter] string $password,
        int $begin,
        int $end,
        string $token,
        private readonly int $day,
        private readonly int $month,
        private readonly int $year,
        private readonly string $separator,
    ) {
        parent::__construct($password, $begin, $end, $token);
    }

    /** The day portion of the date in the token. */
    public function day(): int
    {
        return $this->day;
    }

    /** The month portion of the date in the token. */
    public function month(): int
    {
        return $this->month;
    }

    /** The year portion of the date in the token. */
    public function year(): int
    {
        return $this->year;
    }

    /** The separator used for the date in the token. */
    public function separator(): string
    {
        return $this->separator;
    }
}
