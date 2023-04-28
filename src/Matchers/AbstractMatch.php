<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers;

abstract class AbstractMatch implements MatchInterface
{
    public const PATTERN = self::PATTERN;

    public function __construct(
        #[\SensitiveParameter] private readonly string $password,
        private readonly int $begin,
        private readonly int $end,
        private readonly string $token
    ) {
    }

    public function password(): string
    {
        return $this->password;
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

    public static function getPattern(): string
    {
        return static::PATTERN;
    }
}
