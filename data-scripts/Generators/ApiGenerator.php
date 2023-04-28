<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiGenerator implements GeneratorInterface
{
    private readonly HttpClientInterface $httpClient;
    private readonly SimpleListGenerator $simpleListGenerator;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
        private readonly string $url,
    ) {
        $this->httpClient = HttpClient::create();
        $this->simpleListGenerator = new SimpleListGenerator($this->options, $this->url);

        if ($this->options->hasOccurrences && '|' !== $this->options->occurrenceSeparator) {
            throw new \LogicException('The option occurrenceSeparator must be set to "|" for the ApiGenerator.');
        }
    }

    public function run(): array
    {
        $data = $this->getData();
        $data = $this->lineArrayToString($data);

        return $this->simpleListGenerator->clean($data);
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->options;
    }

    public function lineArrayToString(array $data): array
    {
        $modified = [];
        foreach ($data as $line) {
            if (!\is_array($line)) {
                throw new \RuntimeException('Line is not an array');
            }
            $modified[] = implode($this->options->occurrenceSeparator, $line);
        }

        return $modified;
    }

    public function getData(): array
    {
        return $this->httpClient->request('GET', $this->url, ['query' => $this->options->requestOptions?->query ?? []])->toArray();
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;

        $this->simpleListGenerator->setIo($io);
    }
}
