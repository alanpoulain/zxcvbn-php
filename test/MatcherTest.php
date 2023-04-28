<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Matchers\MatchInterface;

#[CoversClass(Matcher::class)]
final class MatcherTest extends TestCase
{
    public function testGetMatches(): void
    {
        $matcher = new Matcher();
        $password = 'jjjjj';
        $matches = array_values(array_filter($matcher->getMatches($password), static fn (MatchInterface $match) => $match->token() === $password));

        self::assertSame('repeat', $matches[0]::getPattern(), 'Pattern incorrect');
        self::assertCount(1, $matches);
    }

    public function testEmptyString(): void
    {
        $matcher = new Matcher();

        self::assertEmpty($matcher->getMatches(''), "doesn't match ''");
    }

    public function testMultiplePatterns(): void
    {
        $matcher = new Matcher();
        $password = 'r0sebudmaelstrom11/20/91aaaa';

        $expectedMatches = [
            ['dictionary', [0,   6]],
            ['dictionary', [7,  15]],
            ['date',       [16, 23]],
            ['repeat',     [24, 27]],
        ];

        $matches = $matcher->getMatches($password);
        foreach ($matches as $match) {
            $search = array_search([$match::getPattern(), [$match->begin(), $match->end()]], $expectedMatches, true);
            if (false !== $search) {
                unset($expectedMatches[$search]);
            }
        }

        self::assertEmpty($expectedMatches, 'matches multiple patterns');
    }

    /**
     * There's a similar test in DictionaryMatcherTest for this as well, but this specific test is for ensuring that
     * the user input gets passed from the Matcher class through to DictionaryMatch class.
     */
    public function testUserDefinedWords(): void
    {
        $matcher = new Matcher();
        $matches = $matcher->getMatches('_wQbgL491', ['PJnD', 'WQBG', 'ZhwZ']);

        self::assertInstanceOf(DictionaryMatch::class, $matches[0], 'user input match is correct class');
        self::assertSame('wQbg', $matches[0]->token(), 'user input match has correct token');
    }

    public function testInvalidAdditionalMatcher(): void
    {
        $matcher = new Matcher(Configurator::getOptions(new Config(additionalMatchers: ['invalid className'])));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Matcher class must implement ZxcvbnPhp\Matchers\MatcherInterface');

        $matcher->getMatches('abc');
    }
}
