<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Multibyte;

final class MultibyteLevenshtein
{
    public static function mbLevenshtein(string $string1, string $string2, int $insertionCost = 1, int $replacementCost = 1, int $deletionCost = 1): int
    {
        $charMap = [];
        $asciiString1 = self::multibyteToAscii($string1, $charMap);
        $asciiString2 = self::multibyteToAscii($string2, $charMap);

        return levenshtein($asciiString1, $asciiString2, $insertionCost, $replacementCost, $deletionCost);
    }

    /**
     * Convert a UTF-8 encoded string to a single-byte string suitable for functions such as levenshtein.
     *
     * It uses (and updates) a tailored dynamic encoding (in/out map parameter) where non-ascii characters are remapped to the range [128-255] in order of appearance.
     *
     * Thus, it supports up to 128 different multibyte code points max over the whole set of strings sharing this encoding.
     *
     * @param string $string  UTF-8 string to be converted to extended ASCII
     * @param array  $charMap reference of the map
     */
    private static function multibyteToAscii(string $string, array &$charMap): string
    {
        // Find all utf-8 characters.
        $matches = [];
        if (!preg_match_all('/[\xC0-\xF7][\x80-\xBF]+/', $string, $matches)) {
            return $string; // Plain ascii string.
        }

        // Update the encoding map with the characters not already met.
        $mapCount = \count($charMap);
        foreach ($matches[0] as $mbc) {
            if (!isset($charMap[$mbc])) {
                $charMap[$mbc] = \chr(128 + $mapCount);
                ++$mapCount;
            }
        }

        // Remap non-ascii characters.
        return strtr($string, $charMap);
    }
}
