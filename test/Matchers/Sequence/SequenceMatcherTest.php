<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Sequence;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatch;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatcher;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(SequenceMatcher::class)]
#[CoversClass(SequenceMatch::class)]
final class SequenceMatcherTest extends AbstractMatchTestCase
{
    public static function provideShortPasswordCases(): iterable
    {
        return [
            [''],
            ['a'],
            ['1'],
        ];
    }

    #[DataProvider('provideShortPasswordCases')]
    public function testShortPassword(string $password): void
    {
        $matches = SequenceMatcher::match($password, Configurator::getOptions(new Config()));

        self::assertEmpty($matches, "doesn't match length-".\strlen($password).' sequences');
    }

    public function testNonSequence(): void
    {
        $password = 'password';
        $matches = SequenceMatcher::match($password, Configurator::getOptions(new Config()));

        self::assertEmpty($matches, "doesn't match password that's not a sequence");
    }

    public function testOverlappingPatterns(): void
    {
        $password = 'abcbabc';

        $this->checkMatches(
            'matches overlapping patterns',
            SequenceMatcher::match($password, Configurator::getOptions(new Config())),
            'sequence',
            ['abc', 'cba', 'abc'],
            [[0, 2], [2, 4], [4, 6]],
            [
                'ascending' => [true, false, true],
            ]
        );
    }

    public function testEmbeddedSequencePatterns(): void
    {
        $prefixes = ['!', '22'];
        $suffixes = ['!', '22'];
        $pattern = 'jihg';

        foreach ($this->generatePasswords($pattern, $prefixes, $suffixes) as [$password, $begin, $end]) {
            $this->checkMatches(
                'matches embedded sequence patterns',
                SequenceMatcher::match($password, Configurator::getOptions(new Config())),
                'sequence',
                [$pattern],
                [[$begin, $end]],
                [
                    'sequenceName' => ['lower'],
                    'ascending' => [false],
                ]
            );
        }
    }

    public static function provideSequenceInformationCases(): iterable
    {
        return [
            ['ABC',   'upper', 1791,  true],
            ['CBA',   'upper', 1791,  false],
            ['PQR',   'upper', 1791,  true],
            ['RQP',   'upper', 1791,  false],
            ['XYZ',   'upper', 1791,  true],
            ['ZYX',   'upper', 1791,  false],
            ['abcd',  'lower', 2155,  true],
            ['dcba',  'lower', 2155,  false],
            ['jihg',  'lower', 2155,  false],
            ['wxyz',  'lower', 2155,  true],
            ['zxvt',  'lower', 2155,  false],
            ['0369',  'digits', 1781, true],
            ['97531', 'digits', 1781, false],
        ];
    }

    #[DataProvider('provideSequenceInformationCases')]
    public function testSequenceInformation(string $password, string $name, int $space, bool $ascending): void
    {
        $this->checkMatches(
            'matches '.$password.' as a '.$name.' sequence',
            SequenceMatcher::match($password, Configurator::getOptions(new Config())),
            'sequence',
            [$password],
            [[0, \strlen($password) - 1]],
            [
                'sequenceName' => [$name],
                'sequenceSpace' => [$space],
                'ascending' => [$ascending],
            ]
        );
    }

    public function testMultipleMatches(): void
    {
        $password = 'pass123wordZYX';

        $this->checkMatches(
            'matches password with multiple sequences',
            SequenceMatcher::match($password, Configurator::getOptions(new Config())),
            'sequence',
            ['123', 'ZYX'],
            [[4, 6], [11, 13]],
            [
                'sequenceName' => ['digits', 'upper'],
                'ascending' => [true, false],
            ]
        );
    }

    public function testMultibytePassword(): void
    {
        $password = 'muÃeca';

        $this->checkMatches(
            'detects sequence in a multibyte password',
            SequenceMatcher::match($password, Configurator::getOptions(new Config())),
            'sequence',
            ['eca'],
            [[3, 5]],
            [
                'sequenceName' => ['lower'],
                'ascending' => [false],
            ]
        );
    }

    public function testMultibyteSequence(): void
    {
        $password = 'αβγδεζኼኻኺኹኸ';

        $this->checkMatches(
            'detects sequence consisting of multibyte characters',
            SequenceMatcher::match($password, Configurator::getOptions(new Config())),
            'sequence',
            ['αβγδεζ', 'ኼኻኺኹኸ'],
            [[0, 5], [6, 10]],
            [
                'sequenceName' => ['lower', 'unicode'],
                'ascending' => [true, false],
            ]
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('sequence', SequenceMatcher::getPattern());
    }
}
