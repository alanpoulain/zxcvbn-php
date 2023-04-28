<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Result;

use ZxcvbnPhp\Matchers\MatchInterface;

final readonly class ScorerResult
{
    public function __construct(
        public float $guesses,
        public float $guessesLog10,
        /** @var MatchInterface[] */
        public array $sequence,
    ) {
    }
}
