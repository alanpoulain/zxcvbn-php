<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Spatial;

use ZxcvbnPhp\Matchers\AbstractMatch;

final class SpatialMatch extends AbstractMatch
{
    public const PATTERN = 'spatial';

    public function __construct(
        #[\SensitiveParameter] string $password,
        int $begin,
        int $end,
        string $token,
        private readonly string $graph,
        private readonly int $shiftedCount,
        private readonly int $turns
    ) {
        parent::__construct($password, $begin, $end, $token);
    }

    /** The keyboard layout that the token is a spatial match on. */
    public function graph(): string
    {
        return $this->graph;
    }

    /** The number of characters the shift key was held for in the token. */
    public function shiftedCount(): int
    {
        return $this->shiftedCount;
    }

    /** The number of turns on the keyboard required to complete the token. */
    public function turns(): int
    {
        return $this->turns;
    }
}
