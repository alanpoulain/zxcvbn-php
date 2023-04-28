<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Multibyte;

final class MultibyteReg
{
    public const START_UPPER = '/^\p{Lu}\P{Lu}+$/u';
    public const END_UPPER = '/^\P{Lu}+\p{Lu}$/u';
    public const ALL_UPPER = '/^\p{Lu}+$/u';
    public const ALL_UPPER_INVERTED = '/^\P{Ll}+$/u';
    public const ALL_LOWER = '/^\p{Ll}+$/u';
    public const ALL_LOWER_INVERTED = '/^\P{Lu}+$/u';
    public const ONE_LOWER = '/\p{Ll}/u';
    public const ONE_UPPER = '/\p{Lu}/u';
    public const ALPHA_INVERTED = '/[^\p{Lu}\p{Ll}]/u';
    public const ALL_NUMBER = '/^\p{N}+$/u';

    /**
     * Find all occurrences of regular expression in a string.
     *
     * @param string $string string to search
     * @param string $regex  regular expression with captures
     *
     * @return MbRegMatch[][] array of capture groups
     *                        e.g. fishfish /(fish)/
     *                        [[MbRegMatch(begin: 0, end: 3, token: 'fish'), MbRegMatch(begin: 0, end: 3, token: 'fish')],
     *                        [MbRegMatch(begin: 4, end: 7, token: 'fish'), MbRegMatch(begin: 4, end: 7, token: 'fish')]]
     */
    public static function mbRegMatchAll(string $string, string $regex, int $offset = 0): array
    {
        // $offset is the number of multibyte-aware number of characters to offset, but the offset parameter for
        // preg_match_all counts bytes, not characters: to correct this, we need to calculate the byte offset and pass
        // that in instead.
        $charsBeforeOffset = mb_substr($string, 0, $offset);
        $byteOffset = \strlen($charsBeforeOffset);

        $count = preg_match_all($regex, $string, $matches, \PREG_SET_ORDER, $byteOffset);
        if (!$count) {
            return [];
        }

        $groups = [];
        foreach ($matches as $group) {
            $captureBegin = 0;
            $match = array_shift($group);
            $matchBegin = mb_strpos($string, $match, $offset);
            $captures = [new MbRegMatch(begin: $matchBegin, end: $matchBegin + mb_strlen($match) - 1, token: $match)];
            foreach ($group as $capture) {
                $captureBegin = mb_strpos($match, $capture, $captureBegin);
                $captures[] = new MbRegMatch(begin: $matchBegin + $captureBegin, end: $matchBegin + $captureBegin + mb_strlen($capture) - 1, token: $capture);
            }
            $groups[] = $captures;
            $offset += mb_strlen($match) - 1;
        }

        return $groups;
    }
}
