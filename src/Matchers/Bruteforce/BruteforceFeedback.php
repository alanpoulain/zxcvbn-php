<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Bruteforce;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result\FeedbackResult;

final class BruteforceFeedback implements FeedbackInterface
{
    public static function getFeedback(MatchInterface $match, Options $options, bool $isSoleMatch = true): FeedbackResult
    {
        return new FeedbackResult();
    }

    public static function getPattern(): string
    {
        return BruteforceMatch::PATTERN;
    }
}
