<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Configuration;

use Symfony\Component\Finder\Finder;
use ZxcvbnPhp\Config;
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

final class Configurator
{
    private const DEFAULT_MATCHERS = [
        DateMatcher::class,
        DictionaryMatcher::class,
        L33tMatcher::class,
        RepeatMatcher::class,
        ReverseDictionaryMatcher::class,
        SeparatorMatcher::class,
        SequenceMatcher::class,
        SpatialMatcher::class,
        YearMatcher::class,
    ];
    private const DEFAULT_SCORERS = [
        BruteforceScorer::class,
        DateScorer::class,
        DictionaryScorer::class,
        RepeatScorer::class,
        SeparatorScorer::class,
        SequenceScorer::class,
        SpatialScorer::class,
    ];
    private const DEFAULT_FEEDBACKS = [
        BruteforceFeedback::class,
        DateFeedback::class,
        DictionaryFeedback::class,
        RepeatFeedback::class,
        SeparatorFeedback::class,
        SequenceFeedback::class,
        SpatialFeedback::class,
    ];

    private const DEFAULT_DICTIONARY_LANGUAGES = ['common', 'en'];
    private const DEFAULT_TRANSLATION_LOCALE = 'en';

    public static function getOptions(Config $config): Options
    {
        return new Options(
            matchers: array_values(array_unique(array_merge($config->matchers ?? self::DEFAULT_MATCHERS, $config->additionalMatchers))),
            scorers: array_values(array_unique(array_merge($config->scorers ?? self::DEFAULT_SCORERS, $config->additionalScorers))),
            feedbacks: array_values(array_unique(array_merge($config->feedbacks ?? self::DEFAULT_FEEDBACKS, $config->additionalFeedbacks))),
            rankedDictionaries: $rankedDictionaries = self::loadRankedDictionaries($config->dictionaryLanguages ?? self::DEFAULT_DICTIONARY_LANGUAGES, $config->additionalDictionaries),
            rankedDictionariesMaxWordSize: self::getRankedDictionariesMaxWordSize($rankedDictionaries),
            graphs: $config->graphs ?? self::loadGraphs(),
            l33tTable: $config->l33tTable ?? self::loadL33tTable(),
            l33tMaxSubstitutions: $config->l33tMaxSubstitutions,
            useLevenshteinDistance: $config->useLevenshteinDistance,
            levenshteinThreshold: $config->levenshteinThreshold,
            translationEnabled: $config->translationEnabled,
            translationLocale: $config->translationLocale ?? self::DEFAULT_TRANSLATION_LOCALE,
            calcTimeEnabled: $config->calcTimeEnabled,
        );
    }

    /**
     * @param array<string, string[]> $rankedDictionaries
     * @param array<string, int>      $rankedDictionariesMaxWordSize
     * @param string[]                $userInputs
     *
     * @return array{0: array<string, string[]>, 1: array<string, int>}
     */
    public static function getRankedDictionariesWithUserInputs(array $rankedDictionaries, array $rankedDictionariesMaxWordSize, array $userInputs): array
    {
        $sanitizedInputs = self::sanitizeDictionary($userInputs);

        $rankedDictionariesWithUserInputs = array_merge_recursive(['userInputs' => self::buildRankedDictionary($sanitizedInputs)], $rankedDictionaries);

        return [
            $rankedDictionariesWithUserInputs,
            array_merge($rankedDictionariesMaxWordSize, ['userInputs' => self::getDictionaryMaxWordSize(array_keys($rankedDictionariesWithUserInputs['userInputs']))]),
        ];
    }

    private static function loadRankedDictionaries(array $dictionaryLanguages, array $additionalDictionaries): array
    {
        $rankedDictionaries = [];

        foreach ($dictionaryLanguages as $dictionaryLanguage) {
            $rankedDictionaries += self::loadRankedLanguageDictionaries($dictionaryLanguage);
        }

        $rankedAdditionalDictionaries = [];
        foreach ($additionalDictionaries as $additionalDictionaryName => $additionalDictionary) {
            $rankedAdditionalDictionaries[$additionalDictionaryName] = self::buildRankedDictionary(self::sanitizeDictionary($additionalDictionary));
        }

        return $rankedAdditionalDictionaries + $rankedDictionaries;
    }

    /**
     * @return array<string, string[]>
     */
    private static function loadRankedLanguageDictionaries(string $language): array
    {
        $dictionaries = [];

        $finder = Finder::create()->in(sprintf('%s/%s', Options::RESOURCES_PATH, $language))->notName(['adjacencyGraphs.json', 'l33tTable.json', 'translations.json'])->files();
        foreach ($finder as $dictionaryFile) {
            $dictionary = file_get_contents($dictionaryFile->getRealPath());
            if (!$dictionary) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $dictionary = json_decode($dictionary, true, 512, \JSON_THROW_ON_ERROR);
            $dictionaries[sprintf('%s-%s', $language, $dictionaryFile->getFilenameWithoutExtension())] = self::buildRankedDictionary($dictionary);
        }

        return $dictionaries;
    }

    private static function buildRankedDictionary(array $dictionary): array
    {
        if (empty($dictionary)) {
            return [];
        }

        return array_combine($dictionary, range(1, \count($dictionary)));
    }

    private static function getRankedDictionariesMaxWordSize(array $rankedDictionaries): array
    {
        $dictionariesMaxWordSize = [];
        foreach ($rankedDictionaries as $name => $rankedDictionary) {
            $dictionariesMaxWordSize[$name] = self::getDictionaryMaxWordSize(array_keys($rankedDictionary));
        }

        return $dictionariesMaxWordSize;
    }

    private static function getDictionaryMaxWordSize(array $dictionary): int
    {
        $maxWordSize = 0;
        foreach ($dictionary as $word) {
            if (mb_strlen((string) $word) > $maxWordSize) {
                $maxWordSize = mb_strlen((string) $word);
            }
        }

        return $maxWordSize;
    }

    private static function loadGraphs(): array
    {
        $graphsFilepath = sprintf('%s/%s/adjacencyGraphs.json', Options::RESOURCES_PATH, 'common');

        $graphsContent = file_get_contents($graphsFilepath);

        return json_decode($graphsContent, true, 512, \JSON_THROW_ON_ERROR);
    }

    private static function loadL33tTable(): array
    {
        $graphsFilepath = sprintf('%s/%s/l33tTable.json', Options::RESOURCES_PATH, 'common');

        $graphsContent = file_get_contents($graphsFilepath);

        return json_decode($graphsContent, true, 512, \JSON_THROW_ON_ERROR);
    }

    private static function sanitizeDictionary(array $dictionary): array
    {
        return array_map(
            static fn ($input) => mb_strtolower((string) $input),
            $dictionary
        );
    }
}
