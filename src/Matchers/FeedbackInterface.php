<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers;

use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result\FeedbackResult;

interface FeedbackInterface
{
    /**
     * Get feedback to a user based on the match.
     *
     * @param bool $isSoleMatch whether this is the only match in the password
     */
    public static function getFeedback(MatchInterface $match, Options $options, bool $isSoleMatch = true): FeedbackResult;

    public static function getPattern(): string;
}
