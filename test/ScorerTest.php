<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Result\ScorerResult;
use ZxcvbnPhp\Scorer;
use ZxcvbnPhp\Test\Matchers\MockMatch;
use ZxcvbnPhp\Test\Matchers\MockScorer;

#[CoversClass(Scorer::class)]
#[CoversClass(ScorerResult::class)]
final class ScorerTest extends TestCase
{
    public const PASSWORD = '0123456789';

    private Scorer $scorer;

    protected function setUp(): void
    {
        $this->scorer = new Scorer(Configurator::getOptions(new Config(additionalScorers: [MockScorer::class])));
    }

    public function testBlankPassword(): void
    {
        $result = $this->scorer->getMostGuessableMatchSequence('', []);

        self::assertSame(1.0, $result->guesses);
        self::assertEmpty($result->sequence);
    }

    public function testEmptyMatchSequence(): void
    {
        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, []);

        self::assertCount(1, $result->sequence, 'result.sequence.length == 1');
        self::assertSame(10000000001.0, $result->guesses, 'result.guesses == 10000000001');

        $match = $result->sequence[0];
        self::assertSame('bruteforce', $match::getPattern(), "match.pattern == 'bruteforce'");
        self::assertSame(self::PASSWORD, $match->token(), 'match.token == '.self::PASSWORD);
        self::assertSame([0, 9], [$match->begin(), $match->end()], '[begin, end] == [0, 9]');
    }

    public function testMatchAndBruteforceWithPrefix(): void
    {
        $match = new MockMatch(0, 5, 1);

        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, [$match], true);

        self::assertCount(2, $result->sequence, 'result.sequence.length == 2');
        self::assertSame($match, $result->sequence[0], 'first match is the provided match object');

        $match1 = $result->sequence[1];

        self::assertSame('bruteforce', $match1::getPattern(), 'second match is bruteforce');
        self::assertSame([6, 9], [$match1->begin(), $match1->end()], 'second match covers full suffix after first match');
    }

    public function testMatchAndBruteforceWithSuffix(): void
    {
        $match = new MockMatch(3, 9, 1);

        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, [$match], true);

        self::assertCount(2, $result->sequence, 'result.sequence.length == 2');
        self::assertSame($match, $result->sequence[1], 'second match is the provided match object');

        $match0 = $result->sequence[0];

        self::assertSame('bruteforce', $match0::getPattern(), 'first match is bruteforce');
        self::assertSame([0, 2], [$match0->begin(), $match0->end()], 'first match covers full prefix before second match');
    }

    public function testMatchAndBruteforceWithInfix(): void
    {
        $match = new MockMatch(1, 8, 1);

        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, [$match], true);

        self::assertCount(3, $result->sequence, 'result.sequence.length == 3');

        $match0 = $result->sequence[0];
        $match2 = $result->sequence[2];

        self::assertSame($match, $result->sequence[1], 'middle match is the provided match object');
        self::assertSame('bruteforce', $match0::getPattern(), 'first match is bruteforce');
        self::assertSame('bruteforce', $match2::getPattern(), 'third match is bruteforce');
        self::assertSame([0, 0], [$match0->begin(), $match0->end()], 'first match covers full prefix before second match');
        self::assertSame([9, 9], [$match2->begin(), $match2->end()], 'third match covers full suffix after second match');
    }

    public function testChoosesLowerGuessesMatchesForSameSpan(): void
    {
        $matches = [
            new MockMatch(0, 9, 1),
            new MockMatch(0, 9, 2),
        ];

        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, $matches, true);

        self::assertCount(1, $result->sequence, 'result.sequence.length == 1');
        self::assertSame($matches[0], $result->sequence[0], 'result.sequence[0] == m0');
    }

    public function testChoosesLowerGuessesMatchesForSameSpanReversedOrder(): void
    {
        $matches = [
            new MockMatch(0, 9, 2),
            new MockMatch(0, 9, 1),
        ];

        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, $matches, true);

        self::assertCount(1, $result->sequence, 'result.sequence.length == 1');
        self::assertSame($matches[1], $result->sequence[0], 'result.sequence[0] == m1');
    }

    public function testChoosesSupersetMatchWhenApplicable(): void
    {
        $matches = [
            new MockMatch(0, 9, 3),
            new MockMatch(0, 3, 2),
            new MockMatch(4, 9, 1),
        ];

        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, $matches, true);

        self::assertSame(3.0, $result->guesses, 'total guesses == 3');
        self::assertSame([$matches[0]], $result->sequence, 'sequence is [m0] (m0 < m1 * m2 * fact(2))');
    }

    public function testChoosesSubsetMatchesWhenApplicable(): void
    {
        $matches = [
            new MockMatch(0, 9, 5),
            new MockMatch(0, 3, 2),
            new MockMatch(4, 9, 1),
        ];

        $result = $this->scorer->getMostGuessableMatchSequence(self::PASSWORD, $matches, true);

        self::assertSame(4.0, $result->guesses, 'total guesses == 4');
        self::assertSame([$matches[1], $matches[2]], $result->sequence, 'sequence is [m1, m2] (m0 > m1 * m2 * fact(2))');
    }

    public function testNotFoundScorer(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Class with pattern mock not found');

        (new Scorer())->getMostGuessableMatchSequence(self::PASSWORD, [new MockMatch(0, 5, 1)]);
    }
}
