<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary\Result;

final readonly class L33tChangeResult
{
    public function __construct(
        public int $index,
        public string $letter,
        public string $substitution,
    ) {
    }
}
