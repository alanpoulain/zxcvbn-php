<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts\Generators;

interface GeneratorInterface
{
    public function run(): array;

    public function getOptions(): GeneratorOptions;
}
