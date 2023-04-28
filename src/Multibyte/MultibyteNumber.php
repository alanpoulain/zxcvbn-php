<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Multibyte;

final class MultibyteNumber
{
    private const DIGIT = '/(\d)/u';

    public static function convertToInteger(string $number): int
    {
        if (is_numeric($number)) {
            return (int) $number;
        }

        if (class_exists(\IntlChar::class)) {
            $int = 0;
            $rank = 1;
            foreach (MultibyteReg::mbRegMatchAll(MultibyteString::mbStrRev($number), self::DIGIT) as $matches) {
                $int += \IntlChar::charDigitValue(mb_ord($matches[0]->token(), 'UTF-8')) * $rank;
                $rank *= 10;
            }

            return $int;
        }

        // @codeCoverageIgnoreStart
        return -1;
        // @codeCoverageIgnoreEnd
    }
}
