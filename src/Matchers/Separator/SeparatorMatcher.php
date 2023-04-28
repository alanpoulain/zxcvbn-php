<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Separator;

use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Options;

final class SeparatorMatcher implements MatcherInterface
{
    public static array $separators = [
        ' ' => ' ',
        ',' => ',',
        ';' => ';',
        ':' => ':',
        '\|' => '|',
        '\/' => '/',
        '\\\\' => '\\',
        '_' => '_',
        '\.' => '.',
        '-' => '-',
    ];

    /**
     * Match any semi-repeated special character.
     *
     * @return SeparatorMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        $matches = [];

        $mostUsedSeparator = self::getMostUsedSeparator($password);
        if (null === $mostUsedSeparator) {
            return $matches;
        }

        $separatorMatches = [];
        preg_match_all("/(?<!{$mostUsedSeparator})({$mostUsedSeparator})(?!{$mostUsedSeparator})/u", $password, $separatorMatches, \PREG_OFFSET_CAPTURE);
        foreach ($separatorMatches[0] as $separatorMatch) {
            $index = mb_strlen(substr($password, 0, $separatorMatch[1]));

            $matches[] = new SeparatorMatch(
                password: $password,
                begin: $index,
                end: $index,
                token: self::$separators[$mostUsedSeparator]
            );
        }

        return Matcher::sortMatches($matches);
    }

    public static function getPattern(): string
    {
        return SeparatorMatch::PATTERN;
    }

    private static function getMostUsedSeparator(string $password): ?string
    {
        $separatorRegex = '/['.implode('', array_keys(self::$separators)).']/u';
        $passwordCharacters = mb_str_split($password);

        $separatorCount = [];
        foreach ($passwordCharacters as $passwordCharacter) {
            if (preg_match($separatorRegex, $passwordCharacter)) {
                $countKey = array_flip(self::$separators)[$passwordCharacter];
                $separatorCount[$countKey] = ($separatorCount[$countKey] ?? 0) + 1;
            }
        }

        arsort($separatorCount);

        $mostUsedSeparator = null;
        foreach ($separatorCount as $separator => $count) {
            // If the special character is only used once, don't treat it like a separator.
            if ($count > 1) {
                $mostUsedSeparator = $separator;
                break;
            }
        }

        return $mostUsedSeparator;
    }
}
