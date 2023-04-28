<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

final readonly class RequestOptions
{
    public function __construct(
        public array $query,
    ) {
    }
}
