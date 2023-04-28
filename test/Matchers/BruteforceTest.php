<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceFeedback;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatcher;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceScorer;
use ZxcvbnPhp\Result\FeedbackResult;

#[CoversClass(BruteforceMatcher::class)]
#[CoversClass(BruteforceScorer::class)]
#[CoversClass(BruteforceFeedback::class)]
final class BruteforceTest extends AbstractMatchTestCase
{
    public function testMatch(): void
    {
        $password = 'uH2nvQbugW';

        $this->checkMatches(
            'matches entire string',
            BruteforceMatcher::match($password, Configurator::getOptions(new Config())),
            'bruteforce',
            [$password],
            [[0, 9]],
            []
        );
    }

    public function testMultibyteMatch(): void
    {
        $password = '中华人民共和国';

        $this->checkMatches(
            'matches entire string with multibyte characters',
            BruteforceMatcher::match($password, Configurator::getOptions(new Config())),
            'bruteforce',
            [$password],
            [[0, 6]], // It should be 0, 6 and not 0, 20.
            []
        );
    }

    public function testMatcherGetPattern(): void
    {
        self::assertSame('bruteforce', BruteforceMatcher::getPattern());
    }

    public static function provideGuessesCases(): iterable
    {
        return [
            [str_repeat('a', 2), 100],
            [str_repeat('a', 123), 1e123],
            [str_repeat('a', 308), 1e308],
        ];
    }

    #[DataProvider('provideGuessesCases')]
    public function testGuesses(string $token, float $guesses): void
    {
        $match = new BruteforceMatch($token, 0, \strlen($token) - 1, $token);

        self::assertEqualsWithDelta($guesses, BruteforceScorer::getGuesses($match, Configurator::getOptions(new Config())), $guesses * 10 ** -15, 'guesses should be the exponentiation of 10 and the token length');
    }

    public function testGuessesMax(): void
    {
        $token = str_repeat('a', 309);
        $match = new BruteforceMatch($token, 0, \strlen($token) - 1, $token);

        self::assertSame(\PHP_FLOAT_MAX, BruteforceScorer::getGuesses($match, Configurator::getOptions(new Config())), 'long string returns max float guesses');
    }

    public function testGuessesMinSingleCharacter(): void
    {
        $token = 'a';
        $match = new BruteforceMatch($token, 0, \strlen($token) - 1, $token);

        self::assertSame(11.0, BruteforceScorer::getGuesses($match, Configurator::getOptions(new Config())), 'min guesses is one guess bigger than the smallest allowed');
    }

    public function testGuessesMultibyteCharacter(): void
    {
        $token = '🙂'; // smiley face emoji
        $match = new BruteforceMatch($token, 0, 1, $token);

        self::assertSame(11.0, BruteforceScorer::getGuesses($match, Configurator::getOptions(new Config())), 'multibyte character treated as one character');
    }

    public function testScorerGetPattern(): void
    {
        self::assertSame('bruteforce', BruteforceScorer::getPattern());
    }

    public function testFeedback(): void
    {
        $match = new BruteforceMatch(
            password: 'abc',
            begin: 0,
            end: 2,
            token: 'abc'
        );

        self::assertEquals(new FeedbackResult(), BruteforceFeedback::getFeedback($match, Configurator::getOptions(new Config())));
    }

    public function testFeedbackGetPattern(): void
    {
        self::assertSame('bruteforce', BruteforceFeedback::getPattern());
    }
}
