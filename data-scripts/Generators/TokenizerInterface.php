<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

interface TokenizerInterface
{
    public function tokenize(string $line): array;
}
