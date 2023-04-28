<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary;

use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\Dictionary\Variants\L33tVariant;
use ZxcvbnPhp\Matchers\Dictionary\Variants\UppercaseVariant;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Options;

final class DictionaryScorer extends AbstractScorer
{
    protected static function getRawGuesses(MatchInterface $match, Options $options): float
    {
        if (!is_a($match, DictionaryMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', DictionaryMatch::class));
        }

        if ('common-diceware' === $match->dictionaryName()) {
            // Diceware dictionaries are special, so we get a simple scoring of 1/2 of 6^5 (6 digits on 5 dice)
            // to get a fixed entropy of ~12.9 bits for every entry (see https://en.wikipedia.org/wiki/Diceware).
            return 6 ** 5 / 2;
        }

        $guesses = $match->rank();
        $guesses *= UppercaseVariant::getVariations($match);
        $guesses *= L33tVariant::getVariations($match);

        if ($match->reversed()) {
            $guesses *= 2;
        }

        return $guesses;
    }

    public static function getPattern(): string
    {
        return DictionaryMatch::PATTERN;
    }
}
