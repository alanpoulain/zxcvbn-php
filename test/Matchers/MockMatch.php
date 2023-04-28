<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers;

use ZxcvbnPhp\Matchers\AbstractMatch;

final class MockMatch extends AbstractMatch
{
    public const PATTERN = 'mock';

    public function __construct(
        readonly int $begin,
        readonly int $end,
        public readonly float $guesses,
    ) {
        parent::__construct('', $begin, $end, '');
    }
}
