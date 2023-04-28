<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Separator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Separator\SeparatorMatch;
use ZxcvbnPhp\Matchers\Separator\SeparatorScorer;

#[CoversClass(SeparatorScorer::class)]
final class SeparatorScorerTest extends TestCase
{
    public function testGuesses(): void
    {
        $match = new SeparatorMatch(
            password: 'one_two_three',
            begin: 3,
            end: 3,
            token: '_',
        );

        self::assertSame(
            10.,
            SeparatorScorer::getGuesses($match, Configurator::getOptions(new Config())),
            'constant guesses (number of possible separators)'
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('separator', SeparatorScorer::getPattern());
    }
}
