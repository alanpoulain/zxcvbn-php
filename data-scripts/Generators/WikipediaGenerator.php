<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WikipediaGenerator implements GeneratorInterface
{
    private const DATA_CACHE_PATH = 'data-scripts/data-cache';
    private const WIKIEXTRACTOR_PATH = 'data-scripts/wikiextractor';

    /**
     * After each batch, delete all tokens with count == 1 (hapax legomena).
     */
    private const LINES_PER_BATCH = 500000;

    private readonly HttpClientInterface $httpClient;
    private readonly Filesystem $filesystem;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
        private readonly string $url,
        private readonly TokenizerInterface $tokenizer = new UnicodeTokenizer(),
    ) {
        $this->httpClient = HttpClient::create();
        $this->filesystem = new Filesystem();
    }

    public function run(): array
    {
        $pathElements = explode('/', parse_url($this->url, \PHP_URL_PATH));
        $filename = end($pathElements);
        $cacheDirPath = sprintf('%s/%s', self::DATA_CACHE_PATH, $this->options->language);
        $filePath = sprintf('%s/%s', $cacheDirPath, $filename);

        $this->downloadFile($filename, $filePath);

        $extractsDirPath = sprintf('%s/extracts', $cacheDirPath);
        $this->extractData($filePath, $extractsDirPath);

        return $this->getTokens($extractsDirPath);
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->options;
    }

    private function downloadFile(string $filename, string $filePath): void
    {
        if ($this->filesystem->exists($filePath)) {
            $this->io?->info('File has already been downloaded');

            return;
        }

        $dlBefore = 0;
        $response = $this->httpClient->request('GET', $this->url, [
            'query' => $this->options->requestOptions?->query ?? [],
            'on_progress' => function (int $dlNow, int $dlSize) use (&$dlBefore): void {
                try {
                    $this->io?->progressAdvance($dlNow - $dlBefore);
                    $dlBefore = $dlNow;
                } catch (RuntimeException) {
                    if (0 !== $dlSize) {
                        $this->io->progressStart($dlSize);
                    }
                }
            },
        ]);

        $this->io?->info(sprintf('Downloading %s file', $filename));
        foreach ($this->httpClient->stream($response) as $chunk) {
            $this->filesystem->appendToFile($filePath, $chunk->getContent());
        }
        $this->io?->progressFinish();
    }

    private function extractData(string $filePath, string $extractsDirPath): void
    {
        if ($this->filesystem->exists($extractsDirPath)) {
            $this->io?->info('Data have already been extracted');

            return;
        }

        $this->io?->section('Extracting data');

        $this->io?->info('Cloning wikiextractor');
        $process = new Process(['git', 'clone', 'https://github.com/santhoshtr/wikiextractor.git', self::WIKIEXTRACTOR_PATH]);
        $process->mustRun();

        $process = new Process(['python3', '-m', 'wikiextractor.WikiExtractor', '--no-templates', '-o', sprintf('../../%s', $extractsDirPath), sprintf('../../%s', $filePath)]);
        $process->setWorkingDirectory(self::WIKIEXTRACTOR_PATH);
        $process->setTimeout(null);
        $process->start();
        foreach ($process as $data) {
            if (str_starts_with($data, 'INFO: ')) {
                $this->io?->info(substr($data, \strlen('INFO: ')));
                continue;
            }
            if (str_starts_with($data, 'WARNING: ')) {
                $this->io?->warning(substr($data, \strlen('WARNING: ')));
                continue;
            }
            $this->io?->info($data);
        }
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->io?->info('Removing wikiextractor');
        $process = new Process(['rm', '-rf', self::WIKIEXTRACTOR_PATH]);
        $process->mustRun();
    }

    /**
     * @return string[]
     */
    private function getTokens(string $extractsDirPath): array
    {
        $this->io?->info('Get tokens from extracted data');

        $counter = new TopTokensCounter();
        $finder = new Finder();
        $finder->files()->in(sprintf('%s/*', $extractsDirPath))->name('wiki_*');

        $nbLines = 0;
        foreach ($finder as $file) {
            $fileContents = $file->getContents();
            $lines = explode("\n", $fileContents);
            $nbLines += \count($lines);
        }

        $this->io?->progressStart($nbLines);

        $nbLines = 0;
        foreach ($finder as $file) {
            $fileContents = $file->getContents();
            $lines = explode("\n", $fileContents);
            foreach ($lines as $line) {
                if (!str_starts_with($line, '<doc') && !str_starts_with($line, '</doc>')) {
                    $tokens = $this->tokenizer->tokenize($line);
                    $counter->addTokens($tokens);
                }
                ++$nbLines;
                $this->io?->progressAdvance();
                if ($nbLines % self::LINES_PER_BATCH) {
                    $counter->prune();
                }
            }
        }

        $this->io?->progressFinish();

        $counter->preSortPrune();
        $count = $counter->getSortedCount();

        return array_keys($count);
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;
    }
}
