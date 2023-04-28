<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Result\CrackTimesDisplayResult;
use ZxcvbnPhp\Result\CrackTimesSecondsResult;
use ZxcvbnPhp\Result\FeedbackResult;

final readonly class Result
{
    public function __construct(
        #[\SensitiveParameter] public string $password,
        public float $guesses,
        public float $guessesLog10,
        /** @var MatchInterface[] */
        public array $sequence,
        public CrackTimesSecondsResult $crackTimesSeconds,
        public CrackTimesDisplayResult $crackTimesDisplay,
        public int $score,
        public FeedbackResult $feedback,
        /** Time elapsed in seconds with microseconds. */
        public float $calcTime,
    ) {
    }
}
