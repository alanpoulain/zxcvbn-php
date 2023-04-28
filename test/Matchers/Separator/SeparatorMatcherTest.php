<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Separator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Separator\SeparatorMatcher;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(SeparatorMatcher::class)]
final class SeparatorMatcherTest extends AbstractMatchTestCase
{
    public function testsEmptyPassword(): void
    {
        self::assertSame([], SeparatorMatcher::match('', Configurator::getOptions(new Config())));
    }

    public static function provideSameSeparatorsCases(): iterable
    {
        return [
            [' '],
            [','],
            [';'],
            [':'],
            ['|'],
            ['/'],
            ['\\'],
            ['_'],
            ['.'],
            ['-'],
        ];
    }

    #[DataProvider('provideSameSeparatorsCases')]
    public function testSameSeparators(string $separator): void
    {
        $this->checkMatches(
            'matches same separators',
            SeparatorMatcher::match("first{$separator}second{$separator}third", Configurator::getOptions(new Config())),
            'separator',
            [$separator, $separator],
            [[5, 5], [12, 12]],
            []
        );
    }

    public function testOneSeparator(): void
    {
        self::assertEmpty(
            SeparatorMatcher::match('first_second,', Configurator::getOptions(new Config())),
            "doesn't match one separator"
        );
    }

    public function testDifferentPotentialSeparators(): void
    {
        $this->checkMatches(
            'matches with different potential separators',
            SeparatorMatcher::match('first,-second|-third-fourth,', Configurator::getOptions(new Config())),
            'separator',
            ['-', '-', '-'],
            [[6, 6], [14, 14], [20, 20]],
            []
        );
    }

    public function testMultibyte(): void
    {
        $this->checkMatches(
            'matches a multibyte string with different potential separators',
            SeparatorMatcher::match('こんにちは,-鳥|-木-どうも,', Configurator::getOptions(new Config())),
            'separator',
            ['-', '-', '-'],
            [[6, 6], [9, 9], [11, 11]],
            []
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('separator', SeparatorMatcher::getPattern());
    }
}
