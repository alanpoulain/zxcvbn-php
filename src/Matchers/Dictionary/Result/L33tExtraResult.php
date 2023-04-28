<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary\Result;

final readonly class L33tExtraResult
{
    public function __construct(
        public int $begin,
        public int $end,
        /** @var L33tChangeResult[] */
        public array $changes,
        public string $changesDisplay,
    ) {
    }
}
