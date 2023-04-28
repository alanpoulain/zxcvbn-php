<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Spatial;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result\FeedbackResult;

final class SpatialFeedback implements FeedbackInterface
{
    public static function getFeedback(MatchInterface $match, Options $options, bool $isSoleMatch = true): FeedbackResult
    {
        if (!is_a($match, SpatialMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', SpatialMatch::class));
        }

        $warning = 1 === $match->turns() ? 'warnings.straightRow' : 'warnings.keyPattern';

        return new FeedbackResult(
            warning: $warning,
            suggestions: [
                'suggestions.longerKeyboardPattern',
            ],
        );
    }

    public static function getPattern(): string
    {
        return SpatialMatch::PATTERN;
    }
}
