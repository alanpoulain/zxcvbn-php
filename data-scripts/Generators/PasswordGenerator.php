<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Options;

/**
 * Generates a password list from another one.
 *
 * Passwords that both:
 * - Fully match according to zxcvbn-php's matching algorithms other than dictionaries,
 * - Have a higher rank than the corresponding match guess number.
 *
 * Are excluded from the final password list, since zxcvbn-php would score them lower through
 * other means anyhow.
 * In practice this rules out dates and years most often and makes room for more useful data.
 */
final class PasswordGenerator implements GeneratorInterface
{
    private readonly HttpClientInterface $httpClient;
    private readonly Matcher $matcher;
    private readonly Options $options;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $generatorOptions,
        private readonly string $url,
    ) {
        $this->httpClient = HttpClient::create();
        $this->matcher = new Matcher();
        $this->options = Configurator::getOptions(new Config());
    }

    public function run(): array
    {
        $data = $this->splitData($this->getData());

        $passwords = [];
        foreach ($this->io?->progressIterate($data) as $i => $password) {
            $rank = $i + 1;
            if ($this->shouldInclude($password, $rank)) {
                $passwords[] = $password;
            }
        }

        $this->io?->info(sprintf('%d passwords generated', \count($passwords)));

        return $passwords;
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->generatorOptions;
    }

    private function getData(): string
    {
        return $this->httpClient->request('GET', $this->url, ['query' => $this->generatorOptions->requestOptions?->query ?? []])->getContent();
    }

    private function splitData(string $data): array
    {
        return explode($this->generatorOptions->splitter, $data);
    }

    private function shouldInclude(string $password, int $rank): bool
    {
        if (empty($password)) {
            return false;
        }

        $matches = $this->matcher->getMatches($password);
        foreach ($matches as $match) {
            // Only keep matches that span full password.
            if (0 !== $match->begin() || $match->end() !== (\strlen($password) - 1)) {
                continue;
            }
            // Ignore dictionaries.
            if ($match::getPattern() === DictionaryMatch::getPattern()) {
                continue;
            }
            // Filter out this entry: non-dictionary matching will assign a lower guess estimate.
            if (Options::getClassByPattern($this->options->scorers, $match::getPattern())::getGuesses($match, $this->options) < $rank) {
                return false;
            }
        }

        return true;
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;
    }
}
