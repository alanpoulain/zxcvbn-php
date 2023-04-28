<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers;

use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Matchers\MatchInterface;

abstract class AbstractMatchTestCase extends TestCase
{
    /**
     * Takes a pattern and list of prefixes/suffixes.
     * Returns a bunch of variants of that pattern embedded
     * with each possible prefix/suffix combination, including no prefix/suffix.
     *
     * @return array a list of triplets [variant, begin, end] where [begin, end] is the start/end of the pattern, inclusive
     */
    protected function generatePasswords(string $pattern, array $prefixes, array $suffixes): array
    {
        $output = [];

        if (!\in_array('', $prefixes, true)) {
            array_unshift($prefixes, '');
        }
        if (!\in_array('', $suffixes, true)) {
            array_unshift($suffixes, '');
        }

        foreach ($prefixes as $prefix) {
            foreach ($suffixes as $suffix) {
                $begin = \strlen($prefix);
                $end = \strlen($prefix) + \strlen($pattern) - 1;

                $output[] = [
                    $prefix.$pattern.$suffix,
                    $begin,
                    $end,
                ];
            }
        }

        return $output;
    }

    /**
     * @param string                  $prefix       this is prepended to the message of any checks that are run
     * @param MatchInterface[]        $matches
     * @param array|string            $patternNames array of pattern names, or a single pattern which will be repeated
     * @param string[]                $patterns
     * @param array{0: int, 1: int}[] $beginEnds
     */
    protected function checkMatches(
        string $prefix,
        array $matches,
        array|string $patternNames,
        array $patterns,
        array $beginEnds,
        array $props
    ): void {
        if (\is_string($patternNames)) {
            // shortcut: if checking for a list of the same type of patterns,
            // allow passing a string 'pat' instead of array ['pat', 'pat', ...]
            $patternNames = array_fill(0, \count($patterns), $patternNames);
        }

        self::assertCount(
            \count($patterns),
            $matches,
            $prefix.': matches.length == '.\count($patterns)
        );

        foreach ($patterns as $k => $pattern) {
            $match = $matches[$k];
            $patternName = $patternNames[$k];
            $pattern = $patterns[$k];
            [$begin, $end] = $beginEnds[$k];

            self::assertSame(
                $patternName,
                $match::getPattern(),
                "{$prefix} matches[{$k}].pattern == '{$patternName}'"
            );
            self::assertSame(
                [$begin, $end],
                [$match->begin(), $match->end()],
                "{$prefix} matches[{$k}] should have [begin, end] of [{$begin}, {$end}]"
            );
            self::assertSame(
                $pattern,
                $match->token(),
                "{$prefix} matches[{$k}].token == '{$pattern}'"
            );

            foreach ($props as $propName => $propList) {
                $propMessage = var_export($propList[$k], true);
                if (\is_object($propList[$k])) {
                    self::assertEquals(
                        $propList[$k],
                        $match->{$propName}(),
                        "{$prefix} matches[{$k}].{$propName} == {$propMessage}"
                    );
                } else {
                    self::assertSame(
                        $propList[$k],
                        $match->{$propName}(),
                        "{$prefix} matches[{$k}].{$propName} == {$propMessage}"
                    );
                }
            }
        }
    }
}
