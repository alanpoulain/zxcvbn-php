<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Date;

final class Now
{
    public static function getYear(): int
    {
        return (int) date('Y');
    }
}
