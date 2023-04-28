<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Limelight\Config\Config;
use Limelight\Limelight;
use Limelight\Plugins\Library\Romaji\Romaji;
use Symfony\Component\Console\Style\SymfonyStyle;

final class JapaneseListGenerator implements GeneratorInterface
{
    private readonly SimpleListGenerator $simpleListGenerator;
    private readonly Limelight $limelight;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
        private readonly string $url,
    ) {
        $this->simpleListGenerator = new SimpleListGenerator($this->options, $this->url);
        $this->limelight = new Limelight();
        Config::getInstance()->set(['Romaji' => Romaji::class], 'plugins');
        Config::getInstance()->set('wapuro', 'Romaji', 'style');
    }

    public function run(): array
    {
        $data = [];
        foreach ($this->simpleListGenerator->getData() as $datum) {
            $data = [...$data, ...$this->simpleListGenerator->splitData($datum)];
        }
        $cleanCallables = $this->simpleListGenerator->cleanCallables;
        $pos = array_search($this->simpleListGenerator->convertToLowerCase(...), $cleanCallables, false);
        array_splice($cleanCallables, $pos, 0, [$this->convertToRomaji(...)]);
        $this->simpleListGenerator->cleanCallables = $cleanCallables;

        return $this->simpleListGenerator->clean($data);
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->options;
    }

    public function convertToRomaji(array $data): array
    {
        $this->io?->info('Converting to romaji');

        $modified = [];
        foreach ($data as $line) {
            $result = $this->limelight->parse($line)->string('romaji', ' ');
            if (!$result) {
                continue;
            }
            $modified[] = $result;
        }

        return $modified;
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;

        $this->simpleListGenerator->setIo($io);
    }
}
