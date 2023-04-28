<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result;
use ZxcvbnPhp\Zxcvbn;

#[CoversClass(Zxcvbn::class)]
#[CoversClass(Result::class)]
final class ZxcvbnTest extends TestCase
{
    private Zxcvbn $zxcvbn;
    private Options $options;

    protected function setUp(): void
    {
        $this->zxcvbn = new Zxcvbn();
        $this->options = Configurator::getOptions(new Config());
    }

    public function testMinimumGuessesForMultipleMatches(): void
    {
        $matches = $this->zxcvbn->passwordStrength('rockyou')->sequence;

        // Zxcvbn will return two matches: 'rock' (rank 484) and 'you' (rank 1).
        // If tested alone, the word 'you' would return only 1 guess, but because it's part of a larger password,
        // it should return the minimum number of guesses, which is 50 for a multi-character token.
        self::assertSame(50.0, Options::getClassByPattern($this->options->scorers, $matches[1]::getPattern())::getGuesses($matches[1], $this->options));
    }

    public static function provideZxcvbnSanityCheckCases(): iterable
    {
        return [
            ['password', 0, ['dictionary'], 'less than a second', 3],
            ['65432', 0, ['sequence'], 'less than a second', 101],
            ['sdfgsdfg', 1, ['repeat'], 'less than a second', 2595],
            ['fortitude', 1, ['dictionary'], '1 second', 14831],
            ['dfjkym', 1, ['bruteforce'], '2 minutes', 1000001],
            ['fortitude22', 2, ['dictionary', 'repeat'], '2 minutes', 1493000],
            ['absoluteadnap', 2, ['dictionary', 'dictionary'], '32 minutes', 19333360],
            ['knifeandspoon', 3, ['dictionary', 'dictionary', 'dictionary'], '2 days', 1313056000],
            ['h1dden_26191', 3, ['dictionary', 'bruteforce', 'date'], '3 days', 2254368700],
            ['4rfv1236yhn!', 4, ['spatial', 'sequence', 'bruteforce'], '1 month', 38980000000],
            ['BVidSNqe3oXVyE1996', 4, ['bruteforce', 'date'], 'centuries', 10000000000010000],
        ];
    }

    /**
     * Some basic sanity checks.
     * All the underlying functionalities are tested in more details in their specific classes,
     * but this is just to check that it's all tied together correctly at the end.
     *
     * @param string[] $patterns
     */
    #[DataProvider('provideZxcvbnSanityCheckCases')]
    public function testZxcvbnSanityCheck(string $password, int $score, array $patterns, string $slowHashingDisplay, float $guesses): void
    {
        $result = $this->zxcvbn->passwordStrength($password);

        self::assertSame($password, $result->password, 'zxcvbn result has correct password');
        self::assertSame($score, $result->score, 'zxcvbn result has correct score');
        self::assertSame(
            $slowHashingDisplay,
            $result->crackTimesDisplay->offlineSlowHashing1e4PerSecond,
            'zxcvbn result has correct display time for offline slow hashing'
        );
        self::assertEqualsWithDelta($guesses, $result->guesses, 1.0, 'zxcvbn result has correct guesses');

        $actualPatterns = array_map(static fn ($match) => $match::getPattern(), $result->sequence);
        self::assertSame($patterns, $actualPatterns, 'zxcvbn result has correct patterns');
    }

    public function testOptionsUserDefinedWords(): void
    {
        $zxcvbn = new Zxcvbn(new Config(additionalDictionaries: ['userInputs' => ['PJnD', 'WQBG', 'ZhwZ']]));
        $result = $zxcvbn->passwordStrength('_wQbgL491');

        self::assertInstanceOf(DictionaryMatch::class, $result->sequence[1], 'user input match is correct class');
        self::assertSame('wQbg', $result->sequence[1]->token(), 'user input match has correct token');
    }

    /**
     * There's a similar test in DictionaryMatcherTest for this as well, but this specific test is for ensuring that
     * the user input gets passed from the Zxcvbn class all the way through to the DictionaryMatch class.
     */
    public function testUserDefinedWords(): void
    {
        $result = $this->zxcvbn->passwordStrength('_wQbgL491', ['PJnD', 'WQBG', 'ZhwZ']);

        self::assertInstanceOf(DictionaryMatch::class, $result->sequence[1], 'user input match is correct class');
        self::assertSame('wQbg', $result->sequence[1]->token(), 'user input match has correct token');
    }

    public function testMultibyteUserDefinedWords(): void
    {
        $result = $this->zxcvbn->passwordStrength('المفاتيح', ['العربية', 'المفاتيح', 'لوحة']);

        self::assertInstanceOf(DictionaryMatch::class, $result->sequence[0], 'user input match is correct class');
        self::assertSame('المفاتيح', $result->sequence[0]->token(), 'user input match has correct token');
    }

    public function testInvalidAdditionalMatcherWillThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new Zxcvbn(new Config(additionalMatchers: ['invalid className'])))->passwordStrength('you');

        $this->expectNotToPerformAssertions();
    }
}
