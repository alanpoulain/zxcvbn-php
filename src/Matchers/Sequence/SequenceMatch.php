<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Sequence;

use ZxcvbnPhp\Matchers\AbstractMatch;

final class SequenceMatch extends AbstractMatch
{
    public const PATTERN = 'sequence';

    public const SEQUENCE_NAME_LOWER = 'lower';
    public const SEQUENCE_NAME_UPPER = 'upper';
    public const SEQUENCE_NAME_DIGITS = 'digits';
    public const SEQUENCE_NAME_UNICODE = 'unicode';

    public function __construct(
        #[\SensitiveParameter] string $password,
        int $begin,
        int $end,
        string $token,
        private readonly string $sequenceName,
        private readonly int $sequenceSpace,
        private readonly bool $ascending,
    ) {
        parent::__construct($password, $begin, $end, $token);
    }

    /** The name of the detected sequence. */
    public function sequenceName(): string
    {
        return $this->sequenceName;
    }

    /** The number of characters in the complete sequence space. */
    public function sequenceSpace(): int
    {
        return $this->sequenceSpace;
    }

    /** If the sequence is ascending or descending. */
    public function ascending(): bool
    {
        return $this->ascending;
    }
}
