<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Date;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Date\DateMatch;
use ZxcvbnPhp\Matchers\Date\DateMatcher;
use ZxcvbnPhp\Matchers\Date\DateResult;
use ZxcvbnPhp\Matchers\Date\DayMonthResult;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(DateMatcher::class)]
#[CoversClass(DateMatch::class)]
#[CoversClass(DateResult::class)]
#[CoversClass(DayMonthResult::class)]
final class DateMatcherTest extends AbstractMatchTestCase
{
    public static function provideSeparatorsCases(): iterable
    {
        return [
            [''],
            [' '],
            ['-'],
            ['/'],
            ['\\'],
            ['_'],
            ['.'],
        ];
    }

    #[DataProvider('provideSeparatorsCases')]
    public function testSeparators(string $sep): void
    {
        $password = "13{$sep}2{$sep}1921";

        $this->checkMatches(
            "matches dates that use '{$sep}' as a separator",
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'separator' => [$sep],
                'year' => [1921],
                'month' => [2],
                'day' => [13],
            ]
        );
    }

    public function testDateOrders(): void
    {
        [$d, $m, $y] = [8, 8, 88];
        $orders = ['mdy', 'dmy', 'ymd', 'ydm'];
        foreach ($orders as $order) {
            $password = str_replace(
                ['y', 'm', 'd'],
                [$y, $m, $d],
                $order
            );
            $this->checkMatches(
                "matches dates with {$order} format",
                DateMatcher::match($password, Configurator::getOptions(new Config())),
                'date',
                [$password],
                [[0, \strlen($password) - 1]],
                [
                    'separator' => [''],
                    'year' => [1988],
                    'month' => [8],
                    'day' => [8],
                ]
            );
        }
    }

    public static function provideMatchesClosestToReferenceYearCases(): iterable
    {
        return [
            ['21086', 2, 10, 1986], // picks "86" -> 1986 as year, not "1086"
            ['111524', 15, 11, 2024], // picks "24" -> 2024 as year, not "1524"
            ['31250', 3, 12, 2050], // picks "50" -> 2050 as year, not "1950"
            ['31251', 3, 12, 1951], // picks "51" -> 1951 as year, not "2051"
            'maximal two-digit year' => ['15699', 15, 6, 1999],
        ];
    }

    #[DataProvider('provideMatchesClosestToReferenceYearCases')]
    public function testMatchesClosestToReferenceYear(string $password, int $day, int $month, int $year): void
    {
        $this->checkMatches(
            'matches the date with year closest to reference year when ambiguous',
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'separator' => [''],
                'year' => [$year],
                'month' => [$month],
                'day' => [$day],
            ]
        );
    }

    public static function provideNormalDatesCases(): iterable
    {
        return [
            'one-digit day and month' => [1, 1, 1999],
            'one-digit month' => [11, 8, 2000],
            'one-digit day' => [9, 12, 1551],
            'two-digit day and month, maximal day' => [31, 10, 2024],
            'minimal year' => [15, 8, 1000],
            'maximal year' => [15, 8, 2050],
        ];
    }

    #[DataProvider('provideNormalDatesCases')]
    public function testNormalDatesWithoutSeparator(int $day, int $month, int $year): void
    {
        $password = "{$year}{$month}{$day}";

        $this->checkMatches(
            "matches {$password} without a separator",
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'separator' => [''],
                'year' => [$year],
            ]
        );
    }

    #[DataProvider('provideNormalDatesCases')]
    public function testNormalDatesWithSeparator(int $day, int $month, int $year): void
    {
        $password = "{$year}.{$month}.{$day}";

        $this->checkMatches(
            "matches {$password} with a separator",
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'separator' => ['.'],
                'year' => [$year],
            ]
        );
    }

    public function testComplexDateWithoutSeparator(): void
    {
        $this->checkMatches(
            'matches 199577013 without a separator',
            DateMatcher::match('199577013', Configurator::getOptions(new Config())),
            'date',
            ['199577', '57701', '7013'],
            [[0, 5], [3, 7], [5, 8]],
            [
                'separator' => ['', '', ''],
                'day' => [7, 7, 1],
                'month' => [7, 1, 3],
                'year' => [1995, 1957, 1970],
            ]
        );
    }

    public function testComplexDateWithSeparator(): void
    {
        $this->checkMatches(
            'matches 199577013 with a separator',
            DateMatcher::match('1/1/9a1995/12/013', Configurator::getOptions(new Config())),
            'date',
            ['1995/12/01', '95/12/013'],
            [[6, 15], [8, 16]],
            [
                'separator' => ['/', '/'],
                'day' => [12, 13],
                'month' => [1, 12],
                'year' => [1995, 1995],
            ]
        );
    }

    public function testMatchesZeroPaddedDates(): void
    {
        $password = '02/02/02';

        $this->checkMatches(
            'matches zero-padded dates',
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'separator' => ['/'],
                'year' => [2002],
                'month' => [2],
                'day' => [2],
            ]
        );
    }

    public function testFullDateMatched(): void
    {
        $password = '2024-01-20';

        $this->checkMatches(
            'matches full date and not just year',
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'separator' => ['-'],
                'year' => [2024],
                'month' => [1],
                'day' => [20],
            ]
        );
    }

    public function testMatchesEmbeddedDates(): void
    {
        $prefixes = ['a', 'ab'];
        $suffixes = ['!'];
        $pattern = '1/1/91';

        foreach ($this->generatePasswords($pattern, $prefixes, $suffixes) as [$password, $begin, $end]) {
            $this->checkMatches(
                'matches embedded dates',
                DateMatcher::match($password, Configurator::getOptions(new Config())),
                'date',
                [$pattern],
                [[$begin, $end]],
                [
                    'year' => [1991],
                    'month' => [1],
                    'day' => [1],
                ]
            );
        }
    }

    public function testMatchesOverlappingDates(): void
    {
        $password = '12/20/1991.12.20';

        $this->checkMatches(
            'matches overlapping dates',
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            ['12/20/1991', '1991.12.20'],
            [[0, 9], [6, 15]],
            [
                'separator' => ['/', '.'],
                'year' => [1991, 1991],
                'month' => [12, 12],
                'day' => [20, 20],
            ]
        );
    }

    public function testMatchesDatesPadded(): void
    {
        $password = '912/20/919';

        $this->checkMatches(
            'matches dates padded by non-ambiguous digits',
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            ['12/20/91'],
            [[1, 8]],
            [
                'separator' => ['/'],
                'year' => [1991],
                'month' => [12],
                'day' => [20],
            ]
        );
    }

    public function testMultibyteDateWithoutSeparator(): void
    {
        $password = '๙๑๐๑๙๙๖';

        $this->checkMatches(
            'matches multibyte dates',
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            ['๙๑๐๑๙๙๖'],
            [[0, 6]],
            [
                'separator' => [''],
                'year' => [1996],
                'month' => [10],
                'day' => [9],
            ]
        );
    }

    public function testMultibyteDateWithSeparator(): void
    {
        $password = '၀၅/၁၅/၂၀၂၄';

        $this->checkMatches(
            'matches multibyte dates',
            DateMatcher::match($password, Configurator::getOptions(new Config())),
            'date',
            ['၀၅/၁၅/၂၀၂၄'],
            [[0, 9]],
            [
                'separator' => ['/'],
                'year' => [2024],
                'month' => [5],
                'day' => [15],
            ]
        );
    }

    public static function provideNonDatesCases(): iterable
    {
        return [
            'invalid day' => ['30-31-00'],
            'month (or day) > 31' => ['09-32-10'],
        ];
    }

    #[DataProvider('provideNonDatesCases')]
    public function testNonDates(string $date): void
    {
        self::assertEmpty(DateMatcher::match($date, Configurator::getOptions(new Config())), "no match on invalid date {$date}");
    }

    public function testYearUnderMinYear(): void
    {
        $this->checkMatches(
            'does not match year under min year',
            DateMatcher::match('01-10-500', Configurator::getOptions(new Config())),
            'date',
            ['01-10-50'],
            [[0, 7]],
            [
                'separator' => ['-'],
                'day' => [1],
                'month' => [10],
                'year' => [2050],
            ]
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('date', DateMatcher::getPattern());
    }
}
