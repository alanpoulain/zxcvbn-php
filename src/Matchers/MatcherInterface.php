<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers;

use ZxcvbnPhp\Options;

interface MatcherInterface
{
    /**
     * Match this password.
     *
     * @param string   $password   password to check for match
     * @param string[] $userInputs array of values related to the user (optional)
     *
     * @return MatchInterface[] array of Match objects
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array;

    public static function getPattern(): string;
}
