<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Style\SymfonyStyle;
use ZxcvbnPhp\KeyboardLayouts\KeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\KeyboardLayouts;

/**
 * Generates adjacency graphs from keyboard layouts.
 */
final class KeyboardAdjacencyGraphGenerator implements GeneratorInterface
{
    private ?SymfonyStyle $io = null;

    public function __construct(private readonly GeneratorOptions $options)
    {
    }

    public function run(): array
    {
        $graphs = [];

        foreach (KeyboardLayouts::getKeyboardLayouts() as $keyboardLayout) {
            $this->io?->info(sprintf('Generate adjacency graph for keyboard layout %s', $keyboardLayout::getName()));

            $graphs[$keyboardLayout::getName()] = $this->buildGraph($keyboardLayout);
        }

        return $graphs;
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->options;
    }

    /**
     * Builds an adjacency graph as a dictionary: {character: [adjacentCharacters]}.
     * Adjacent characters occur in a clockwise order.
     * For example:
     * - On qwerty layout, 'g' maps to ['fF', 'tT', 'yY', 'hH', 'bB', 'vV'].
     * - On keypad layout, '7' maps to [null, null, null, '=', '8', '5', '4', null].
     *
     * @param class-string<KeyboardLayout> $keyboardLayout
     */
    private function buildGraph(string $keyboardLayout): array
    {
        $tokens = $this->getTokens($keyboardLayout);
        $tokenSize = mb_strlen($tokens[0]);
        $xUnit = $tokenSize + 1; // x position unit length is token length plus 1 for the following whitespace.

        foreach ($tokens as $token) {
            if (mb_strlen($token) !== $tokenSize) {
                throw new \RuntimeException(sprintf('Token length mismatch: %s should have a length of %d.', $token, $tokenSize));
            }
        }

        $adjacencyGraph = [];
        $positionTable = $this->getPositionTable($keyboardLayout, $xUnit);
        foreach ($positionTable as $coordinates => $token) {
            [$x, $y] = $this->parseCoordinates($coordinates);
            $adjustedCoordinates = $keyboardLayout::isSlanted() ? $this->getSlantedAdjacentCoordinates($x, $y) : $this->getAlignedAdjacentCoordinates($x, $y);
            $characters = mb_str_split($token);
            foreach ($characters as $character) {
                $adjacencyGraph[$character] = [];
                foreach ($adjustedCoordinates as $adjustedCoordinate) {
                    $adjacencyGraph[$character][] = $positionTable[$adjustedCoordinate] ?? null;
                }
            }
        }

        return $adjacencyGraph;
    }

    /**
     * @param class-string<KeyboardLayout> $keyboardLayout
     *
     * @return string[]
     */
    private function getTokens(string $keyboardLayout): array
    {
        $tokens = [];

        $lines = explode("\n", $keyboardLayout::getLayout());
        foreach ($lines as $line) {
            $tokens = [...$tokens, ...$this->getLineTokens($line)];
        }

        return $tokens;
    }

    /**
     * @return string[]
     */
    private function getLineTokens(string $line): array
    {
        $cleanedLine = preg_replace('/ {2,}/', '', $line);

        return explode(' ', $cleanedLine);
    }

    /**
     * Maps from tuple (x,y) -> characters at that position.
     * Position in the list indicates direction
     * For qwerty, 0 is left, 1 is top, 2 is top right, etc.
     * For edge chars like 1 or m, insert null as a placeholder when needed,
     * so that each character in the graph has a same-length adjacency list.
     *
     * @param class-string<KeyboardLayout> $keyboardLayout
     *
     * @return array{string, string}
     */
    private function getPositionTable(string $keyboardLayout, int $xUnit): array
    {
        $positionTable = [];
        $lines = explode("\n", $keyboardLayout::getLayout());
        foreach ($lines as $i => $line) {
            $slant = $keyboardLayout::isSlanted() ? $i : 0;
            $tokens = $this->getLineTokens($line);
            foreach ($tokens as $token) {
                $value = mb_strpos($line, $token) - $slant;
                $x = floor($value / $xUnit);
                $remainder = $value % $xUnit;
                if (0 !== $remainder) {
                    throw new \RuntimeException(sprintf('Unexpected x offset for %s.', $token));
                }
                $positionTable[sprintf('%d,%d', $x, $i + 1)] = $token;
            }
        }

        return $positionTable;
    }

    /**
     * Returns the six adjacent coordinates on a standard keyboard, where each row is slanted to the
     * right from the last. Adjacencies are clockwise, starting with key to the left, then two keys
     * above, then right key, then two keys below. That is, only near-diagonal keys are adjacent,
     * so g's coordinate is adjacent to those of t,y,b,v, but not those of r,u,n,c.
     *
     * @return string[]
     */
    private function getSlantedAdjacentCoordinates(int $x, int $y): array
    {
        return [
            sprintf('%d,%d', $x - 1, $y), // left
            sprintf('%d,%d', $x, $y - 1), // below left
            sprintf('%d,%d', $x + 1, $y - 1), // below right
            sprintf('%d,%d', $x + 1, $y), // right
            sprintf('%d,%d', $x, $y + 1), // above right
            sprintf('%d,%d', $x - 1, $y + 1), // above left
        ];
    }

    /**
     * Returns the nine clockwise adjacent coordinates on a keypad, where each row is vertically aligned.
     *
     * @return string[]
     */
    private function getAlignedAdjacentCoordinates(int $x, int $y): array
    {
        return [
            sprintf('%d,%d', $x - 1, $y),
            sprintf('%d,%d', $x - 1, $y - 1),
            sprintf('%d,%d', $x, $y - 1),
            sprintf('%d,%d', $x + 1, $y - 1),
            sprintf('%d,%d', $x + 1, $y),
            sprintf('%d,%d', $x + 1, $y + 1),
            sprintf('%d,%d', $x, $y + 1),
            sprintf('%d,%d', $x - 1, $y + 1),
        ];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function parseCoordinates(string $coordinates): array
    {
        $parsedCoordinates = [];
        foreach (explode(',', $coordinates) as $coordinate) {
            $parsedCoordinates[] = (int) $coordinate;
        }

        return $parsedCoordinates;
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;
    }
}
