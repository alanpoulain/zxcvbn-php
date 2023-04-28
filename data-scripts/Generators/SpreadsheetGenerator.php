<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SpreadsheetGenerator implements GeneratorInterface
{
    private readonly SimpleListGenerator $simpleListGenerator;
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly GeneratorOptions $options,
        private readonly string $url,
    ) {
        $this->simpleListGenerator = new SimpleListGenerator($this->options, $this->url);

        if ($this->options->hasOccurrences && '|' !== $this->options->occurrenceSeparator) {
            throw new \LogicException('The option occurrenceSeparator must be set to "|" for the SpreadsheetGenerator.');
        }
    }

    public function run(): array
    {
        $spreadsheetPath = tempnam(sys_get_temp_dir(), 'zxcvbnPhpSpreadsheet');
        $spreadsheetFile = fopen($spreadsheetPath, 'wb');
        fwrite($spreadsheetFile, $this->simpleListGenerator->getData()[0]);
        fclose($spreadsheetFile);

        $data = [];

        $isXls = str_ends_with($this->url, '.xls');
        $reader = $isXls ? new Xls() : new Xlsx();
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly($this->options->sheetName ? [$this->options->sheetName] : null);
        $spreadsheet = $reader->load($spreadsheetPath);
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $line = '';
            foreach ($cellIterator as $cell) {
                $line .= $cell->getValue().'|';
            }
            $data[] = $line;
        }
        unlink($spreadsheetPath);

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
