<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceFeedback;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceScorer;
use ZxcvbnPhp\Matchers\Date\DateFeedback;
use ZxcvbnPhp\Matchers\Date\DateMatcher;
use ZxcvbnPhp\Matchers\Date\DateScorer;
use ZxcvbnPhp\Matchers\Date\YearMatcher;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryFeedback;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatcher;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryScorer;
use ZxcvbnPhp\Matchers\Dictionary\L33tMatcher;
use ZxcvbnPhp\Matchers\Dictionary\ReverseDictionaryMatcher;
use ZxcvbnPhp\Matchers\Repeat\RepeatFeedback;
use ZxcvbnPhp\Matchers\Repeat\RepeatMatcher;
use ZxcvbnPhp\Matchers\Repeat\RepeatScorer;
use ZxcvbnPhp\Matchers\Separator\SeparatorFeedback;
use ZxcvbnPhp\Matchers\Separator\SeparatorMatcher;
use ZxcvbnPhp\Matchers\Separator\SeparatorScorer;
use ZxcvbnPhp\Matchers\Sequence\SequenceFeedback;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatcher;
use ZxcvbnPhp\Matchers\Sequence\SequenceScorer;
use ZxcvbnPhp\Matchers\Spatial\SpatialFeedback;
use ZxcvbnPhp\Matchers\Spatial\SpatialMatcher;
use ZxcvbnPhp\Matchers\Spatial\SpatialScorer;
use ZxcvbnPhp\Options;

#[CoversClass(Configurator::class)]
#[CoversClass(Config::class)]
#[CoversClass(Options::class)]
final class ConfiguratorTest extends TestCase
{
    public function testLoadingDefaultOptions(): void
    {
        $options = Configurator::getOptions(new Config());

        self::assertSame([
            DateMatcher::class,
            DictionaryMatcher::class,
            L33tMatcher::class,
            RepeatMatcher::class,
            ReverseDictionaryMatcher::class,
            SeparatorMatcher::class,
            SequenceMatcher::class,
            SpatialMatcher::class,
            YearMatcher::class,
        ], $options->matchers);
        self::assertSame([
            BruteforceScorer::class,
            DateScorer::class,
            DictionaryScorer::class,
            RepeatScorer::class,
            SeparatorScorer::class,
            SequenceScorer::class,
            SpatialScorer::class,
        ], $options->scorers);
        self::assertSame([
            BruteforceFeedback::class,
            DateFeedback::class,
            DictionaryFeedback::class,
            RepeatFeedback::class,
            SeparatorFeedback::class,
            SequenceFeedback::class,
            SpatialFeedback::class,
        ], $options->feedbacks);
        self::assertSame(['userInputs', 'common-diceware', 'common-passwords', 'en-commonWords', 'en-lastnames', 'en-firstnames', 'en-wikipedia'], array_keys($options->rankedDictionaries));
        self::assertSame([], $options->rankedDictionaries['userInputs']);
        self::assertSame(['userInputs', 'common-diceware', 'common-passwords', 'en-commonWords', 'en-lastnames', 'en-firstnames', 'en-wikipedia'], array_keys($options->rankedDictionariesMaxWordSize));
        self::assertSame(0, $options->rankedDictionariesMaxWordSize['userInputs']);
        self::assertSame(9, $options->rankedDictionariesMaxWordSize['common-diceware']);
        self::assertSame([
            'azerty',
            'bepo',
            'dvorak',
            'keypad',
            'keypadMac',
            'nFAzerty',
            'qwerty',
            'qwertz',
        ], array_keys($options->graphs));
        self::assertSame(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'k', 'l', 'm', 'n', 'o', 'q', 'u', 's', 't', 'v', 'w', 'x', 'z'], array_keys($options->l33tTable));
        self::assertSame(50, $options->l33tMaxSubstitutions);
        self::assertFalse($options->useLevenshteinDistance);
        self::assertSame(2, $options->levenshteinThreshold);
        self::assertTrue($options->translationEnabled);
        self::assertSame('en', $options->translationLocale);
        self::assertTrue($options->calcTimeEnabled);
    }

    public function testLoadingCustomOptions(): void
    {
        $options = Configurator::getOptions(new Config(
            matchers: [DictionaryMatcher::class, SequenceMatcher::class],
            additionalMatchers: [DictionaryMatcher::class, YearMatcher::class],
            scorers: [DateScorer::class, SpatialScorer::class, RepeatScorer::class],
            additionalScorers: [DictionaryScorer::class, DateScorer::class, SequenceScorer::class],
            feedbacks: [RepeatFeedback::class, SeparatorFeedback::class],
            additionalFeedbacks: [SpatialFeedback::class, RepeatFeedback::class, SequenceFeedback::class],
            dictionaryLanguages: ['fr'],
            additionalDictionaries: ['test' => ['HELLO', 'world', 42]],
            graphs: ['graph' => ['j' => ['hH', 'uU', 'iI', 'kK', ',?', 'nN']]],
            l33tTable: ['table' => ['a' => ['4', '@']]],
            l33tMaxSubstitutions: 3,
            useLevenshteinDistance: true,
            levenshteinThreshold: 1,
            translationEnabled: false,
            translationLocale: 'fr',
            calcTimeEnabled: false,
        ));

        self::assertSame([DictionaryMatcher::class, SequenceMatcher::class, YearMatcher::class], $options->matchers);
        self::assertSame([DateScorer::class, SpatialScorer::class, RepeatScorer::class, DictionaryScorer::class, SequenceScorer::class], $options->scorers);
        self::assertSame([RepeatFeedback::class, SeparatorFeedback::class, SpatialFeedback::class, SequenceFeedback::class], $options->feedbacks);
        self::assertSame(['test', 'fr-commonWords', 'fr-lastnames', 'fr-firstnames', 'fr-wikipedia'], array_keys($options->rankedDictionaries));
        self::assertSame(['hello' => 1, 'world' => 2, 42 => 3], $options->rankedDictionaries['test']);
        self::assertSame(['test', 'fr-commonWords', 'fr-lastnames', 'fr-firstnames', 'fr-wikipedia'], array_keys($options->rankedDictionariesMaxWordSize));
        self::assertSame(5, $options->rankedDictionariesMaxWordSize['test']);
        self::assertSame(['graph' => ['j' => ['hH', 'uU', 'iI', 'kK', ',?', 'nN']]], $options->graphs);
        self::assertSame(['table' => ['a' => ['4', '@']]], $options->l33tTable);
        self::assertSame(3, $options->l33tMaxSubstitutions);
        self::assertTrue($options->useLevenshteinDistance);
        self::assertSame(1, $options->levenshteinThreshold);
        self::assertFalse($options->translationEnabled);
        self::assertSame('fr', $options->translationLocale);
        self::assertFalse($options->calcTimeEnabled);
    }

    public function testGetRankedDictionariesWithUserInputs(): void
    {
        [$rankedDictionaries, $rankedDictionariesMaxWordSize] = Configurator::getRankedDictionariesWithUserInputs([
            'dict' => ['word' => 1, 'foo' => 2],
        ], [
            'dict' => 4,
        ], ['Γειά σου', 'Κόσμε']);

        self::assertSame(['userInputs' => ['γειά σου' => 1, 'κόσμε' => 2], 'dict' => ['word' => 1, 'foo' => 2]], $rankedDictionaries);
        self::assertSame(['dict' => 4, 'userInputs' => 8], $rankedDictionariesMaxWordSize);
    }
}
