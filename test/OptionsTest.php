<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Matchers\Date\DateScorer;
use ZxcvbnPhp\Matchers\Sequence\SequenceScorer;
use ZxcvbnPhp\Options;

#[CoversClass(Options::class)]
final class OptionsTest extends TestCase
{
    public function testGetClassByPattern(): void
    {
        self::assertSame(SequenceScorer::class, Options::getClassByPattern([DateScorer::class, SequenceScorer::class], 'sequence'));
    }

    public function testGetClassByPatternNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Class with pattern not_existing not found');

        Options::getClassByPattern([DateScorer::class, SequenceScorer::class], 'not_existing');
    }
}
