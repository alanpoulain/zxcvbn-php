<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Multibyte;

final class MultibyteString
{
    public static function mbStrRev(string $string): string
    {
        $chars = mb_str_split($string);

        return implode('', array_reverse($chars));
    }
}
