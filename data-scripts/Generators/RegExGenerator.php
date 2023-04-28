<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Style\SymfonyStyle;

final class RegExGenerator implements GeneratorInterface
{
    private readonly SimpleListGenerator $simpleListGenerator;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
        private readonly string $url,
    ) {
        $this->simpleListGenerator = new SimpleListGenerator($this->options, $this->url);

        if ($this->options->hasOccurrences && '|' !== $this->options->occurrenceSeparator) {
            throw new \LogicException('The option occurrenceSeparator must be set to "|" for the RegExGenerator.');
        }
    }

    public function run(): array
    {
        $initialData = $this->simpleListGenerator->getData();
        $data = [];
        foreach ($this->fromRegEx($initialData) as $datum) {
            $data = [...$data, ...$this->simpleListGenerator->splitData($datum)];
        }

        return $this->simpleListGenerator->clean($data);
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->options;
    }

    public function fromRegEx(array $data): array
    {
        if (!$this->options->regEx) {
            return $data;
        }

        $this->io?->info('Extracting data with regex');

        $modified = [];
        foreach ($data as $content) {
            preg_match_all($this->options->regEx, $content, $matches);
            $modified = [...$modified, ...$matches[1]];
        }

        return $modified;
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;

        $this->simpleListGenerator->setIo($io);
    }
}
