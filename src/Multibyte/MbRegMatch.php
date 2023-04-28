<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Multibyte;

final readonly class MbRegMatch
{
    public function __construct(
        private int $begin,
        private int $end,
        private string $token
    ) {
    }

    public function begin(): int
    {
        return $this->begin;
    }

    public function end(): int
    {
        return $this->end;
    }

    public function token(): string
    {
        return $this->token;
    }
}
