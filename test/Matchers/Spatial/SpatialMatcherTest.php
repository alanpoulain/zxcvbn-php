<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Spatial;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Spatial\SpatialMatch;
use ZxcvbnPhp\Matchers\Spatial\SpatialMatcher;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(SpatialMatcher::class)]
#[CoversClass(SpatialMatch::class)]
final class SpatialMatcherTest extends AbstractMatchTestCase
{
    /**
     * @return string[][]
     */
    public static function provideShortPatternsCases(): iterable
    {
        return [
            [''],
            ['/'],
            ['qw'],
            ['*/'],
        ];
    }

    #[DataProvider('provideShortPatternsCases')]
    public function testShortPatterns(string $password): void
    {
        self::assertSame(
            [],
            SpatialMatcher::match($password, Configurator::getOptions(new Config())),
            "doesn't match 1- and 2-character spatial patterns"
        );
    }

    public function testNoPattern(): void
    {
        self::assertSame(
            [],
            SpatialMatcher::match('qzpm', Configurator::getOptions(new Config())),
            "doesn't match non-pattern"
        );
    }

    public function testSurroundedPattern(): void
    {
        $pattern = '6tfGHJ';
        $password = "rz!{$pattern}%z";

        $options = Configurator::getOptions(new Config());
        // For testing, make a subgraph that contains a single keyboard.
        $graphs = ['qwerty' => $options->graphs['qwerty']];

        $this->checkMatches(
            'matches against spatial patterns surrounded by non-spatial patterns',
            SpatialMatcher::match($password, Configurator::getOptions(new Config(graphs: $graphs))),
            'spatial',
            [$pattern],
            [[3, 8]],
            [
                'graph' => ['qwerty'],
                'turns' => [2],
                'shiftedCount' => [3],
            ]
        );
    }

    public static function provideSpatialPatternsCases(): iterable
    {
        return [
            ['12345',        'qwerty',     1, 0],
            ['@WSX',         'qwerty',     1, 4],
            ['6tfGHJ',       'qwerty',     2, 3],
            ['hGFd',         'qwerty',     1, 2],
            ['/;p09876yhn',  'qwerty',     3, 0],
            ['Xdr%',         'qwerty',     1, 2],
            ['159-',         'keypad',     1, 0],
            ['*84',          'keypad',     1, 0],
            ['/8520',        'keypad',     1, 0],
            ['369',          'keypad',     1, 0],
            ['/963.',        'keypadMac',  1, 0],
            ['*-632.0214',   'keypadMac',  9, 0],
            ['aoEP%yIxkjq:', 'dvorak',     4, 5],
            [';qoaOQ:Aoq;a', 'dvorak',    11, 4],
        ];
    }

    #[DataProvider('provideSpatialPatternsCases')]
    public function testSpatialPatterns(string $password, string $keyboard, int $turns, int $shifts): void
    {
        $options = Configurator::getOptions(new Config());
        $graphs = [$keyboard => $options->graphs[$keyboard]];

        $this->checkMatches(
            "matches '{$password}' as a {$keyboard} pattern",
            SpatialMatcher::match($password, Configurator::getOptions(new Config(graphs: $graphs))),
            'spatial',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'graph' => [$keyboard],
                'turns' => [$turns],
                'shiftedCount' => [$shifts],
            ]
        );
    }

    public function testShiftedCountForMultipleMatches(): void
    {
        $password = '!QAZ1qaz';

        $options = Configurator::getOptions(new Config());
        $graphs = ['qwerty' => $options->graphs['qwerty']];

        $this->checkMatches(
            'shifted count is correct for two matches in a row',
            SpatialMatcher::match($password, Configurator::getOptions(new Config(graphs: $graphs))),
            'spatial',
            ['!QAZ', '1qaz'],
            [[0, 3], [4, 7]],
            [
                'graph' => ['qwerty', 'qwerty'],
                'turns' => [1, 1],
                'shiftedCount' => [4, 0],
            ]
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('spatial', SpatialMatcher::getPattern());
    }
}
