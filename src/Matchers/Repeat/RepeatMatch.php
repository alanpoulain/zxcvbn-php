<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Repeat;

use ZxcvbnPhp\Matchers\AbstractMatch;
use ZxcvbnPhp\Matchers\MatchInterface;

final class RepeatMatch extends AbstractMatch
{
    public const PATTERN = 'repeat';

    public function __construct(
        #[\SensitiveParameter] string $password,
        int $begin,
        int $end,
        string $token,
        /** @var MatchInterface[] */
        private readonly array $baseMatches,
        private readonly float $baseGuesses,
        private readonly int $repeatCount,
        private readonly string $repeatedChar,
    ) {
        parent::__construct($password, $begin, $end, $token);
    }

    /**
     * An array of matches for the repeated section itself.
     *
     * @return MatchInterface[]
     */
    public function baseMatches(): array
    {
        return $this->baseMatches;
    }

    /** The number of guesses required for the repeated section itself. */
    public function baseGuesses(): float
    {
        return $this->baseGuesses;
    }

    /** The number of times the repeated section is repeated. */
    public function repeatCount(): int
    {
        return $this->repeatCount;
    }

    /** The string that was repeated in the token. */
    public function repeatedChar(): string
    {
        return $this->repeatedChar;
    }
}
