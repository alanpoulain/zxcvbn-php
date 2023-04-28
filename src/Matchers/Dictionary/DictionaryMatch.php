<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary;

use ZxcvbnPhp\Matchers\AbstractMatch;

final class DictionaryMatch extends AbstractMatch
{
    public const PATTERN = 'dictionary';

    public function __construct(
        #[\SensitiveParameter] string $password,
        int $begin,
        int $end,
        string $token,
        private readonly string $matchedWord,
        private readonly int $rank,
        private readonly string $dictionaryName,
        private readonly bool $reversed,
        private readonly bool $l33t,
        private readonly int $levenshteinDistance,
        private readonly ?L33tExtraMatch $l33tExtra = null,
    ) {
        parent::__construct($password, $begin, $end, $token);
    }

    /** The word that was matched from the dictionary. */
    public function matchedWord(): string
    {
        return $this->matchedWord;
    }

    /** The rank of the token in the dictionary. */
    public function rank(): int
    {
        return $this->rank;
    }

    /** The name of the dictionary that the token was found in. */
    public function dictionaryName(): string
    {
        return $this->dictionaryName;
    }

    /** Whether the matched word was reversed in the token. */
    public function reversed(): bool
    {
        return $this->reversed;
    }

    /** Whether the token contained l33t substitutions. */
    public function l33t(): bool
    {
        return $this->l33t;
    }

    public function l33tExtra(): ?L33tExtraMatch
    {
        return $this->l33tExtra;
    }

    public function levenshteinDistance(): int
    {
        return $this->levenshteinDistance;
    }
}
