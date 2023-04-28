<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Date;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result\FeedbackResult;

final class DateFeedback implements FeedbackInterface
{
    public static function getFeedback(MatchInterface $match, Options $options, bool $isSoleMatch = true): FeedbackResult
    {
        if (!is_a($match, DateMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', DateMatch::class));
        }

        if (-1 === $match->month()) {
            return new FeedbackResult(
                warning: 'warnings.recentYears',
                suggestions: [
                    'suggestions.recentYears',
                    'suggestions.associatedYears',
                ],
            );
        }

        return new FeedbackResult(
            warning: 'warnings.dates',
            suggestions: [
                'suggestions.dates',
            ],
        );
    }

    public static function getPattern(): string
    {
        return DateMatch::PATTERN;
    }
}
