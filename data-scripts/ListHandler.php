<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use ZxcvbnPhp\Options;
use ZxcvbnPhpDataScripts\Generators\GeneratorInterface;

final class ListHandler
{
    private readonly Filesystem $filesystem;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly string $filename,
        private readonly GeneratorInterface $generator,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function run(): void
    {
        $this->io?->title(sprintf('Starting %s %s', $this->getLanguage(), $this->filename));

        if ($this->io && method_exists($this->generator, 'setIo')) {
            $this->generator->setIo($this->io);
        }
        $data = $this->generator->run();

        if ($data) {
            $folder = sprintf('%s/%s', Options::RESOURCES_PATH, $this->getLanguage());
            $this->filesystem->dumpFile(sprintf('%s/%s.json', $folder, $this->filename), json_encode($data, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE));
        }
    }

    public function getLanguage(): string
    {
        return $this->generator->getOptions()->language;
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;
    }
}
