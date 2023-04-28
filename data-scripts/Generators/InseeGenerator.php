<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use Symfony\Component\Console\Style\SymfonyStyle;

final class InseeGenerator implements GeneratorInterface
{
    private readonly SimpleListGenerator $simpleListGenerator;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
        private readonly string $url,
    ) {
        $this->simpleListGenerator = new SimpleListGenerator($this->options, $this->url);
    }

    public function run(): array
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'zxcvbnPhpInsee');
        $zipFile = fopen($zipPath, 'wb');
        fwrite($zipFile, $this->simpleListGenerator->getData()[0]);
        fclose($zipFile);

        $zipArchive = new \ZipArchive();
        $zipArchive->open($zipPath);
        $content = $zipArchive->getFromIndex(0);
        $zipArchive->close();
        unlink($zipPath);

        $data = $this->simpleListGenerator->splitData($content);

        return $this->simpleListGenerator->clean($data);
    }

    public function getOptions(): GeneratorOptions
    {
        return $this->options;
    }

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;

        $this->simpleListGenerator->setIo($io);
    }
}
