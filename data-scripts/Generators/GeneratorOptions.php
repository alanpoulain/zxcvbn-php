<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

final readonly class GeneratorOptions
{
    public function __construct(
        public string $language,
        public string $splitter = "\n",
        public array $commentPrefixes = ['#', '//'],
        public bool $removeDuplicates = true,
        public bool $emptyLine = true,
        public bool $trimWhitespaces = true,
        public string $convertEncoding = '',
        public bool $toLowerCase = true,
        public int $fromRow = 0,
        public bool $hasOccurrences = false,
        public string $occurrenceSeparator = ' ',
        public int $minOccurrences = 500,
        public int $occurrenceColumn = 1,
        public int $valueColumn = 0,
        public int $minLength = 2,
        public bool $splitCompoundNames = false,
        public string $splitCompoundNamesSeparator = ' ',
        public bool $normalizeDiacritics = false,
        public string $sheetName = '',
        public ?RequestOptions $requestOptions = null,
        public int $pagination = 0,
        public string $regEx = '',
    ) {
    }
}
