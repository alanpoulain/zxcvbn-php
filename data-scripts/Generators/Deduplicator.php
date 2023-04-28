<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use ZxcvbnPhp\Options;

final class Deduplicator
{
    private readonly Filesystem $filesystem;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function deduplicate(array $data): array
    {
        $wikipediaFilepath = sprintf('%s/%s/wikipedia.json', Options::RESOURCES_PATH, $this->options->language);

        if (!$this->filesystem->exists($wikipediaFilepath)) {
            $this->io?->warning('Wikipedia data have not been extracted yet');

            return $data;
        }

        $wikipediaContent = file_get_contents($wikipediaFilepath);
        $wikipediaData = json_decode($wikipediaContent, true, 512, \JSON_THROW_ON_ERROR);

        $numberBetterRankWords = 0;
        $numberDeduplicated = 0;
        foreach ($wikipediaData as $wikipediaRank => $wikipediaWord) {
            // If wikipedia has a better rank, the word can be removed from data.
            if (false !== $rank = array_search($wikipediaWord, $data, true)) {
                if ($rank >= $wikipediaRank) {
                    unset($data[$rank]);
                    ++$numberDeduplicated;
                } else {
                    ++$numberBetterRankWords;
                }
            }
        }

        $this->io?->note(sprintf('%d words deduplicated and %d words not deduplicated (better rank than Wikipedia)', $numberDeduplicated, $numberBetterRankWords));

        return array_values($data);
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;
    }
}
