<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Sequence;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Sequence\SequenceFeedback;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatch;

#[CoversClass(SequenceFeedback::class)]
final class SequenceFeedbackTest extends TestCase
{
    public function testFeedback(): void
    {
        $token = 'rstuvw';
        $match = new SequenceMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            sequenceName: 'lower',
            sequenceSpace: 2155,
            ascending: true
        );
        $feedback = SequenceFeedback::getFeedback($match, Configurator::getOptions(new Config()));

        self::assertSame(
            'warnings.sequences',
            $feedback->warning,
            'sequence gives correct warning'
        );
        self::assertSame(
            ['suggestions.sequences'],
            $feedback->suggestions,
            'sequence gives correct suggestion'
        );
    }

    public function testGetPattern(): void
    {
        self::assertSame('sequence', SequenceFeedback::getPattern());
    }
}
