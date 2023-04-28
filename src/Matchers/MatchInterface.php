<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers;

interface MatchInterface
{
    public function password(): string;

    public function begin(): int;

    public function end(): int;

    public function token(): string;

    public static function getPattern(): string;
}
