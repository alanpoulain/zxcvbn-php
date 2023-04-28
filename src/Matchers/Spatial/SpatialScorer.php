<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Spatial;

use ZxcvbnPhp\Matchers\AbstractScorer;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Math\Binomial;
use ZxcvbnPhp\Options;

final class SpatialScorer extends AbstractScorer
{
    protected static function getRawGuesses(MatchInterface $match, Options $options): float
    {
        if (!is_a($match, SpatialMatch::class)) {
            throw new \LogicException(sprintf('Match object needs to be of class %s', SpatialMatch::class));
        }

        $graph = $options->graphs[$match->graph()];
        $startingPosition = \count($graph);
        $averageDegree = self::calculateAverageDegree($graph);

        $guesses = 0;
        $tokenLength = mb_strlen($match->token());

        // Estimate the number of possible patterns w/ token length or less with turns or less.
        for ($i = 2; $i <= $tokenLength; ++$i) {
            $possibleTurns = min($match->turns(), $i - 1);
            for ($j = 1; $j <= $possibleTurns; ++$j) {
                $guesses += Binomial::binom($i - 1, $j - 1) * $startingPosition * ($averageDegree ** $j);
            }
        }

        // Add extra guesses for shifted keys (% instead of 5, A instead of a).
        // Math is similar to extra guesses of l33t substitutions in dictionary matches.
        if ($match->shiftedCount() > 0) {
            $shiftedCount = $match->shiftedCount();
            $unShiftedCount = $tokenLength - $shiftedCount;

            if (0 === $unShiftedCount) {
                $guesses *= 2;
            } else {
                $shiftedVariations = 0;
                for ($i = 1; $i <= min($shiftedCount, $unShiftedCount); ++$i) {
                    $shiftedVariations += Binomial::binom($shiftedCount + $unShiftedCount, $i);
                }
                $guesses *= $shiftedVariations;
            }
        }

        return $guesses;
    }

    public static function getPattern(): string
    {
        return SpatialMatch::PATTERN;
    }

    private static function calculateAverageDegree(array $graph): float
    {
        $average = 0;
        foreach ($graph as $neighbors) {
            foreach ($neighbors as $neighbor) {
                if (null !== $neighbor) {
                    ++$average;
                }
            }
        }

        $average /= \count($graph);

        return $average;
    }
}
