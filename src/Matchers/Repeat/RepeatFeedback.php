<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Repeat;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result\FeedbackResult;

final class RepeatFeedback implements FeedbackInterface
{
    public static function getFeedback(MatchInterface $match, Options $options, bool $isSoleMatch = true): FeedbackResult
    {
        if (!is_a($match, RepeatMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', RepeatMatch::class));
        }

        $warning = 1 === mb_strlen($match->repeatedChar()) ? 'warnings.simpleRepeat' : 'warnings.extendedRepeat';

        return new FeedbackResult(
            warning: $warning,
            suggestions: [
                'suggestions.repeated',
            ],
        );
    }

    public static function getPattern(): string
    {
        return RepeatMatch::PATTERN;
    }
}
