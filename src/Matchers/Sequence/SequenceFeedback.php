<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Sequence;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result\FeedbackResult;

final class SequenceFeedback implements FeedbackInterface
{
    public static function getFeedback(MatchInterface $match, Options $options, bool $isSoleMatch = true): FeedbackResult
    {
        return new FeedbackResult(
            warning: 'warnings.sequences',
            suggestions: [
                'suggestions.sequences',
            ],
        );
    }

    public static function getPattern(): string
    {
        return SequenceMatch::PATTERN;
    }
}
