<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Matchers\ScorerInterface;

final class Config
{
    public function __construct(
        /** @var class-string<MatcherInterface>[] */
        public ?array $matchers = null,
        /** @var class-string<MatcherInterface>[] */
        public array $additionalMatchers = [],
        /** @var class-string<ScorerInterface>[] */
        public ?array $scorers = null,
        /** @var class-string<ScorerInterface>[] */
        public array $additionalScorers = [],
        /** @var class-string<FeedbackInterface>[] */
        public ?array $feedbacks = null,
        /** @var class-string<FeedbackInterface>[] */
        public array $additionalFeedbacks = [],
        /** @var string[] */
        public ?array $dictionaryLanguages = null,
        /** @var array<string, string[]> */
        public array $additionalDictionaries = [
            'userInputs' => [],
        ],
        /** @var array<string, array> */
        public ?array $graphs = null,
        /** @var array<string, string[]> */
        public ?array $l33tTable = null,
        public int $l33tMaxSubstitutions = 50,
        public bool $useLevenshteinDistance = false,
        public int $levenshteinThreshold = 2,
        public bool $translationEnabled = true,
        public ?string $translationLocale = null,
        public bool $calcTimeEnabled = true,
    ) {
    }
}
