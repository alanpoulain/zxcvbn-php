<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Dictionary;

use PHPUnit\Framework\Attributes\CoversClass;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\AbstractMatch;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Matchers\Dictionary\ReverseDictionaryMatcher;
use ZxcvbnPhp\Test\Matchers\AbstractMatchTestCase;

#[CoversClass(ReverseDictionaryMatcher::class)]
#[CoversClass(AbstractMatch::class)]
#[CoversClass(DictionaryMatch::class)]
final class ReverseDictionaryMatcherTest extends AbstractMatchTestCase
{
    private static array $testDicts = [
        'd1' => [
            '123',
            '321',
            '456',
            '654',
        ],
    ];

    public function testWordWithCustomDictionary(): void
    {
        $password = '0123456789';
        $patterns = ['123', '456'];

        $this->checkMatches(
            'matches against reversed words in custom dictionary',
            ReverseDictionaryMatcher::match($password, Configurator::getOptions(new Config(dictionaryLanguages: [], additionalDictionaries: self::$testDicts))),
            'dictionary',
            $patterns,
            [[1, 3], [4, 6]],
            [
                'matchedWord' => ['321', '654'],
                'reversed' => [true, true],
                'rank' => [2, 4],
                'dictionaryName' => ['d1', 'd1'],
            ]
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('dictionary', ReverseDictionaryMatcher::getPattern());
    }
}
