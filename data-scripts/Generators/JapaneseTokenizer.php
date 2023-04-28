<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Limelight\Config\Config;
use Limelight\Limelight;
use Limelight\Plugins\Library\Romaji\Romaji;

final class JapaneseTokenizer implements TokenizerInterface
{
    private readonly Limelight $limelight;

    public function __construct(
        private readonly TokenizerInterface $tokenizer = new UnicodeTokenizer(),
    ) {
        $this->limelight = new Limelight();
        Config::getInstance()->set(['Romaji' => Romaji::class], 'plugins');
        Config::getInstance()->set('wapuro', 'Romaji', 'style');
    }

    public function tokenize(string $line): array
    {
        return $this->tokenizer->tokenize($this->limelight->parse($line)->string('romaji', ' '));
    }
}
