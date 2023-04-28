<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary\Result;

final readonly class L33tResult
{
    public function __construct(
        public string $password,
        /** @var L33tChangeResult[] */
        public array $changes,
        public bool $isFullSubstitution,
    ) {
    }
}
