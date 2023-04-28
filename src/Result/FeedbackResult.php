<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Result;

final readonly class FeedbackResult
{
    public function __construct(
        public ?string $warning = null,
        /** @var string[] */
        public array $suggestions = [],
    ) {
    }
}
