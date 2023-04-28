<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary;

use ZxcvbnPhp\Matchers\FeedbackInterface;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Multibyte\MultibyteReg;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Result\FeedbackResult;

final class DictionaryFeedback implements FeedbackInterface
{
    public static function getFeedback(MatchInterface $match, Options $options, bool $isSoleMatch = true): FeedbackResult
    {
        if (!is_a($match, DictionaryMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', DictionaryMatch::class));
        }

        $word = $match->token();
        $suggestions = [];
        if (preg_match(MultibyteReg::START_UPPER, $word)) {
            $suggestions[] = 'suggestions.capitalization';
        } elseif (preg_match(MultibyteReg::ALL_UPPER_INVERTED, $word)) {
            $suggestions[] = 'suggestions.allUppercase';
        }
        if ($match->reversed() && mb_strlen($match->token()) >= 4) {
            $suggestions[] = 'suggestions.reverseWords';
        }
        if ($match->l33t()) {
            $suggestions[] = 'suggestions.l33t';
        }

        return new FeedbackResult(
            warning: self::getWarning($match, $options, $isSoleMatch),
            suggestions: $suggestions,
        );
    }

    public static function getPattern(): string
    {
        return DictionaryMatch::PATTERN;
    }

    private static function getWarning(DictionaryMatch $match, Options $options, bool $isSoleMatch): ?string
    {
        if ('common-passwords' === $match->dictionaryName()) {
            return self::getWarningPassword($match, $options, $isSoleMatch);
        }
        if (str_contains($match->dictionaryName(), 'wikipedia')) {
            return self::getWarningWikipedia($isSoleMatch);
        }
        if (str_contains(strtolower($match->dictionaryName()), 'lastnames') || str_contains(strtolower($match->dictionaryName()), 'firstnames')) {
            return self::getWarningName($isSoleMatch);
        }
        if ('userInputs' === $match->dictionaryName()) {
            return 'warnings.userInputs';
        }

        return null;
    }

    private static function getWarningPassword(DictionaryMatch $match, Options $options, bool $isSoleMatch): ?string
    {
        if ($isSoleMatch && !$match->l33t() && !$match->reversed()) {
            if ($match->rank() <= 10) {
                return 'warnings.topTen';
            }
            if ($match->rank() <= 100) {
                return 'warnings.topHundred';
            }

            return 'warnings.common';
        }
        if (log10(DictionaryScorer::getGuesses($match, $options)) <= 4) {
            return 'warnings.similarToCommon';
        }

        return null;
    }

    private static function getWarningWikipedia(bool $isSoleMatch): ?string
    {
        return $isSoleMatch ? 'warnings.wordByItself' : null;
    }

    private static function getWarningName(bool $isSoleMatch): ?string
    {
        return $isSoleMatch ? 'warnings.namesByThemselves' : 'warnings.commonNames';
    }
}
