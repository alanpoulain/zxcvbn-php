<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Polyfill\Intl\Normalizer\Normalizer;

final class SimpleListGenerator implements GeneratorInterface
{
    public array $cleanCallables = [];
    private readonly HttpClientInterface $httpClient;
    private readonly Deduplicator $deduplicator;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
        private readonly string $url,
    ) {
        $this->cleanCallables = [
            $this->fromRow(...),
            $this->emptyLine(...),
            $this->filterOccurrences(...),
            $this->commentPrefixes(...),
            $this->trimWhitespaces(...),
            $this->convertEncoding(...),
            $this->convertToLowerCase(...),
            $this->splitCompoundNames(...),
            $this->normalizeDiacritics(...),
            $this->removeDuplicates(...),
            $this->filterMinLength(...),
            $this->deduplicate(...),
        ];
        $this->httpClient = HttpClient::create();
        $this->deduplicator = new Deduplicator($options);
    }

    public function run(): array
    {
        $data = [];
        foreach ($this->getData() as $datum) {
            $data = [...$data, ...$this->splitData($datum)];
        }

        return $this->clean($data);
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->options;
    }

    public function getData(): array
    {
        if ($this->options->pagination) {
            $data = [];
            for ($i = 0; $i < $this->options->pagination; ++$i) {
                $result = $this->httpClient->request('GET', str_replace('__PAGINATION__', (string) $i, $this->url), ['query' => $this->options->requestOptions?->query ?? []])->getContent();
                $data[] = $result;
            }

            return $data;
        }

        return [$this->httpClient->request('GET', $this->url, ['query' => $this->options->requestOptions?->query ?? []])->getContent()];
    }

    public function splitData(string $data): array
    {
        return explode($this->options->splitter, $data);
    }

    public function clean(array $data): array
    {
        foreach ($this->cleanCallables as $callable) {
            $data = $callable($data);
        }

        return $data;
    }

    public function fromRow(array $data): array
    {
        if (!$this->options->fromRow) {
            return $data;
        }

        $this->io?->info('Removing first rows');

        $modified = [];
        foreach ($data as $i => $line) {
            if ($i < $this->options->fromRow) {
                continue;
            }

            $modified[] = $line;
        }

        return $modified;
    }

    public function emptyLine(array $data): array
    {
        if (!$this->options->emptyLine) {
            return $data;
        }

        $this->io?->info('Filtering empty lines');

        $modified = [];
        foreach ($data as $line) {
            if ('' === $line) {
                continue;
            }
            $modified[] = $line;
        }

        return $modified;
    }

    public function filterOccurrences(array $data): array
    {
        if (!$this->options->hasOccurrences) {
            return $data;
        }

        $this->io?->info('Removing occurrence info');

        $modified = [];
        foreach ($data as $line) {
            $lineData = explode($this->options->occurrenceSeparator, $line);
            if (\count($lineData) < 2) {
                $this->io?->error(sprintf('Line "%s" has no occurrence info', $line));

                continue;
            }
            if (!isset($lineData[$this->options->occurrenceColumn])) {
                throw new \RuntimeException('Option occurrenceColumn is not correctly set.');
            }
            $occurrence = (int) $lineData[$this->options->occurrenceColumn];
            if ($occurrence >= $this->options->minOccurrences) {
                $modified[] = $lineData[$this->options->valueColumn];
            }
        }

        return $modified;
    }

    public function commentPrefixes(array $data): array
    {
        if (!$this->options->commentPrefixes) {
            return $data;
        }

        $this->io?->info('Filtering comments');

        $filtered = [];
        foreach ($this->options->commentPrefixes as $prefix) {
            foreach ($data as $line) {
                if (!str_starts_with($line, $prefix)) {
                    $filtered[] = $line;
                }
            }
        }

        return $filtered;
    }

    public function trimWhitespaces(array $data): array
    {
        if (!$this->options->trimWhitespaces) {
            return $data;
        }

        $this->io?->info('Filtering whitespaces');

        $modified = [];
        foreach ($data as $line) {
            $modified[] = trim($line);
        }

        return $modified;
    }

    public function convertToLowerCase(array $data): array
    {
        if (!$this->options->toLowerCase) {
            return $data;
        }

        $this->io?->info('Converting to lowercase');

        $modified = [];
        foreach ($data as $line) {
            $modified[] = mb_strtolower($line, 'UTF-8');
        }

        return $modified;
    }

    public function splitCompoundNames(array $data): array
    {
        if (!$this->options->splitCompoundNames) {
            return $data;
        }

        $this->io?->info('Splitting compound names');

        $modified = [];
        foreach ($data as $line) {
            $names = explode($this->options->splitCompoundNamesSeparator, $line);
            foreach ($names as $name) {
                $modified[] = $name;
            }
        }

        return $modified;
    }

    public function normalizeDiacritics(array $data): array
    {
        if (!$this->options->normalizeDiacritics) {
            return $data;
        }

        $this->io?->info('Normalizing diacritics');

        $modified = [];
        foreach ($data as $line) {
            $normalized = normalizer_normalize($line, Normalizer::FORM_D);
            $normalized = preg_replace('/[\x{0300}-\x{036f}]/u', '', $normalized);
            $modified[] = $line;
            if ($normalized !== $line) {
                $modified[] = $normalized;
            }
        }

        return $modified;
    }

    public function convertEncoding(array $data): array
    {
        if (!$this->options->convertEncoding) {
            return $data;
        }

        $this->io?->info('Convert encoding');

        $modified = [];
        foreach ($data as $line) {
            try {
                $modified[] = mb_convert_encoding($line, 'UTF-8', $this->options->convertEncoding);
            } catch (\ValueError) {
                $modified[] = iconv($this->options->convertEncoding, 'UTF-8', $line);
            }
        }

        return $modified;
    }

    public function removeDuplicates(array $data): array
    {
        if (!$this->options->removeDuplicates) {
            return $data;
        }

        $this->io?->info('Filtering duplicates');

        return array_values(array_unique($data));
    }

    public function filterMinLength(array $data): array
    {
        if (!$this->options->minLength) {
            return $data;
        }

        $this->io?->info('Filtering password that are too short');

        $filtered = [];
        foreach ($data as $line) {
            if (\strlen($line) >= $this->options->minLength) {
                $filtered[] = $line;
            }
        }

        return $filtered;
    }

    public function deduplicate(array $data): array
    {
        $this->io?->info('Deduplicating using Wikipedia data');

        return $this->deduplicator->deduplicate($data);
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;

        $this->deduplicator->setIo($io);
    }
}
