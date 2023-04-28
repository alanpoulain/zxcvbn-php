<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Matchers\ScorerInterface;

/**
 * @internal
 */
final readonly class Options
{
    public const RESOURCES_PATH = __DIR__.'/Resources/data/languages';

    public function __construct(
        /** @var class-string<MatcherInterface>[] */
        public array $matchers,
        /** @var class-string<ScorerInterface>[] */
        public array $scorers,
        /** @var class-string<FeedbackInterface>[] */
        public array $feedbacks,
        /** @var array<string, string[]> */
        public array $rankedDictionaries,
        /** @var array<string, int> */
        public array $rankedDictionariesMaxWordSize,
        /** @var array<string, array> */
        public array $graphs,
        /** @var array<string, string[]> */
        public array $l33tTable,
        public int $l33tMaxSubstitutions,
        public bool $useLevenshteinDistance,
        public int $levenshteinThreshold,
        public bool $translationEnabled,
        public string $translationLocale,
        public bool $calcTimeEnabled,
    ) {
    }

    /**
     * @template C
     *
     * @param class-string<C>[] $classes
     *
     * @return class-string<C>
     */
    public static function getClassByPattern(array $classes, string $pattern): string
    {
        foreach ($classes as $class) {
            if ($class::getPattern() === $pattern) {
                return $class;
            }
        }

        throw new \RuntimeException(sprintf('Class with pattern %s not found', $pattern));
    }
}
