<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

final class UnicodeTokenizer implements TokenizerInterface
{
    public function tokenize(string $line): array
    {
        return preg_split('/\P{L}/u', $line, -1, \PREG_SPLIT_NO_EMPTY);
    }
}
