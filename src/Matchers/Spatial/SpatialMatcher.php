<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Spatial;

use ZxcvbnPhp\KeyboardLayouts\KeyboardLayouts;
use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;

final class SpatialMatcher implements MatcherInterface
{
    /**
     * Match spatial patterns based on keyboard layouts (e.g. qwerty, dvorak, keypad).
     *
     * @return SpatialMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        $matches = [];
        foreach ($options->graphs as $graphName => $graph) {
            $matches = [...$matches, ...self::graphMatch($password, $graph, $graphName)];
        }

        return Matcher::sortMatches($matches);
    }

    public static function getPattern(): string
    {
        return SpatialMatch::PATTERN;
    }

    /**
     * Match spatial patterns in an adjacency graph.
     *
     * @return MatchInterface[]
     */
    private static function graphMatch(string $password, array $graph, string $graphName): array
    {
        $matches = [];
        $i = 0;
        $passwordLength = mb_strlen($password);

        while ($i < $passwordLength - 1) {
            $j = $i + 1;
            $lastDirection = null;
            $turns = 0;
            $shiftedCount = self::initShiftedCount($graphName, $password, $i);

            while (true) {
                $prevChar = mb_substr($password, $j - 1, 1);
                $adjacents = $graph[$prevChar] ?? [];
                $found = false;
                $curDirection = -1;

                // Consider growing pattern by one character if j hasn't gone over the edge.
                if ($j < $passwordLength) {
                    $curChar = mb_substr($password, $j, 1);
                    foreach ($adjacents as $adjacent) {
                        ++$curDirection;
                        if (null === $adjacent) {
                            continue;
                        }
                        $curCharPos = self::indexOf($adjacent, $curChar);
                        if (-1 === $curCharPos) {
                            continue;
                        }
                        $found = true;
                        $foundDirection = $curDirection;

                        if (1 === $curCharPos) {
                            // Index 1 in the adjacency means the key is shifted, 0 means unshifted: A vs a, % vs 5, etc.
                            // For example, 'q' is adjacent to the entry '2@'. @ is shifted w/ index 1, 2 is unshifted.
                            ++$shiftedCount;
                        }
                        if ($lastDirection !== $foundDirection) {
                            // Adding a turn is correct even in the initial case when last direction is null:
                            // every spatial pattern starts with a turn.
                            ++$turns;
                            $lastDirection = $foundDirection;
                        }

                        break;
                    }
                }

                // If the current pattern continued, extend j and try to grow again.
                if ($found) {
                    ++$j;
                } else {
                    // Otherwise push the pattern discovered so far, if any...

                    // Ignore length 1 or 2 chains.
                    if ($j - $i > 2) {
                        $matches[] = new SpatialMatch(
                            password: $password,
                            begin: $i,
                            end: $j - 1,
                            token: mb_substr($password, $i, $j - $i),
                            graph: $graphName,
                            shiftedCount: $shiftedCount,
                            turns: $turns,
                        );
                    }
                    // ...and then start a new search for the rest of the password.
                    $i = $j;
                    break;
                }
            }
        }

        return $matches;
    }

    private static function initShiftedCount($graphName, $password, $index): int
    {
        if (($shiftedCharacters = KeyboardLayouts::getKeyboardLayoutByName($graphName)::getShiftedCharacters()) !== null
            // Initial character is shifted.
            && false !== mb_strpos($shiftedCharacters, mb_substr($password, $index, 1))) {
            return 1;
        }

        return 0;
    }

    /**
     * Get the index of a string a character first.
     */
    private static function indexOf(string $string, string $char): int
    {
        $pos = mb_strpos($string, $char);

        return false === $pos ? -1 : $pos;
    }
}
