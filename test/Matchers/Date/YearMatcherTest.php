<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Date;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Date\YearMatcher;
use ZxcvbnPhp\Multibyte\MbRegMatch;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(YearMatcher::class)]
#[CoversClass(MbRegMatch::class)]
final class YearMatcherTest extends AbstractMatchTestCase
{
    public function testNoMatchForNonYear(): void
    {
        $password = 'password';

        self::assertEmpty(YearMatcher::match($password, Configurator::getOptions(new Config())));
    }

    public static function provideRecentYearsCases(): iterable
    {
        return [
            ['1922'],
            ['2001'],
            ['2017'],
            ['2023'],
        ];
    }

    #[DataProvider('provideRecentYearsCases')]
    public function testRecentYears(string $password): void
    {
        $this->checkMatches(
            'matches recent year',
            YearMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'day' => [-1],
                'month' => [-1],
                'year' => [(int) $password],
            ]
        );
    }

    public static function provideNonRecentYearsCases(): iterable
    {
        return [
            ['1420'],
            ['1899'],
            ['2030'],
        ];
    }

    #[DataProvider('provideNonRecentYearsCases')]
    public function testNonRecentYears(string $password): void
    {
        $matches = YearMatcher::match($password, Configurator::getOptions(new Config()));

        self::assertEmpty($matches, 'does not match non-recent year');
    }

    public function testYearSurroundedByWords(): void
    {
        $prefixes = ['car', 'dog'];
        $suffixes = ['car', 'dog'];
        $pattern = '1900';

        foreach ($this->generatePasswords($pattern, $prefixes, $suffixes) as [$password, $begin, $end]) {
            $this->checkMatches(
                'identifies years surrounded by words',
                YearMatcher::match($password, Configurator::getOptions(new Config())),
                'date',
                [$pattern],
                [[$begin, $end]],
                [
                    'day' => [-1],
                    'month' => [-1],
                    'year' => [1900],
                ]
            );
        }

        $password = 'password1900';
        $matches = YearMatcher::match($password, Configurator::getOptions(new Config()));
        self::assertCount(1, $matches);
        self::assertSame('1900', $matches[0]->token(), 'Token incorrect');
    }

    public function testYearWithinOtherNumbers(): void
    {
        $password = '419004';

        $this->checkMatches(
            'matches year within other numbers',
            YearMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            ['1900'],
            [[1, 4]],
            [
                'day' => [-1],
                'month' => [-1],
                'year' => [1900],
            ]
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('date', YearMatcher::getPattern());
    }
}
