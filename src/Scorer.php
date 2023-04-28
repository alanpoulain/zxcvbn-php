<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Math\Factorial;
use ZxcvbnPhp\Result\ScorerResult;

/**
 * Takes a list of potential matches, ranks and evaluates them,
 * and figures out how many guesses it would take to crack the password.
 */
final class Scorer
{
    public const MIN_GUESSES_BEFORE_GROWING_SEQUENCE = 10000;
    public const MIN_SUBMATCH_GUESSES_SINGLE_CHAR = 10;
    public const MIN_SUBMATCH_GUESSES_MULTI_CHAR = 50;

    private Options $options;

    private string $password;
    private bool $excludeAdditive;
    private array $optimal = [];

    public function __construct(?Options $options = null)
    {
        $this->options = $options ?? Configurator::getOptions(new Config());
    }

    /**
     * Takes a sequence of overlapping matches, returns the non-overlapping sequence with
     * minimum guesses. The following is a O(l_max * (n + m)) dynamic programming algorithm
     * for a length-n password with m candidate matches. l_max is the maximum optimal
     * sequence length spanning each prefix of the password. In practice, it rarely exceeds 5 and the
     * search terminates rapidly.
     *
     * The optimal "minimum guesses" sequence is here defined to be the sequence that
     * minimizes the following function:
     *
     *    g = l! * Product(m.guesses for m in sequence) + D^(l - 1)
     *
     * where l is the length of the sequence.
     *
     * The factorial term is the number of ways to order l patterns.
     *
     * The D^(l-1) term is another length penalty, roughly capturing the idea that an
     * attacker will try lower-length sequences first before trying length-l sequences.
     *
     * For example, consider a sequence that is date-repeat-dictionary.
     *  - An attacker would need to try other date-repeat-dictionary combinations,
     *    hence the product term.
     *  - An attacker would need to try repeat-date-dictionary, dictionary-repeat-date,
     *    ..., hence the factorial term.
     *  - An attacker would also likely try length-1 (dictionary) and length-2 (dictionary-date)
     *    sequences before length-3. Assuming at minimum D guesses per pattern type,
     *    D^(l-1) approximates Sum(D^i for i in [1..l-1].
     *
     * @param MatchInterface[] $matches
     */
    public function getMostGuessableMatchSequence(#[\SensitiveParameter] string $password, array $matches, bool $excludeAdditive = false): ScorerResult
    {
        $this->password = $password;
        $this->excludeAdditive = $excludeAdditive;

        $passwordLength = mb_strlen($password);
        $initArray = $passwordLength ? array_fill(0, $passwordLength, []) : [];

        // Partition matches into sublists according to ending index.
        $matchesByEndIndex = $initArray;
        foreach ($matches as $match) {
            $matchesByEndIndex[$match->end()][] = $match;
        }

        // Small detail: for deterministic output, sort each sublist by begin index.
        foreach ($matchesByEndIndex as &$matchesToSort) {
            usort($matchesToSort, static function ($a, $b) {
                /* @var $a MatchInterface */
                /* @var $b MatchInterface */
                return $a->begin() - $b->begin();
            });
        }

        $this->optimal = [
            // optimal.m[k][l] holds final match in the best length-l match sequence covering the
            // password prefix up to k, inclusive.
            // If there is no length-l sequence that scores better (fewer guesses) than
            // a shorter match sequence spanning the same prefix, optimal.m[k][l] is undefined.
            'm' => $initArray,

            // Same structure as optimal.m. Holds the product term Prod(m.guesses for m in sequence).
            // optimal.pi allows for fast (non-looping) updates to the minimization function.
            'pi' => $initArray,

            // Same structure as optimal.m. Holds the overall metric.
            'g' => $initArray,
        ];

        for ($k = 0; $k < $passwordLength; ++$k) {
            /** @var MatchInterface $match */
            foreach ($matchesByEndIndex[$k] as $match) {
                if ($match->begin() > 0) {
                    foreach ($this->optimal['m'][$match->begin() - 1] as $sequenceLength => $unused) {
                        $this->update($match, $sequenceLength + 1);
                    }
                } else {
                    $this->update($match, 1);
                }
            }
            $this->bruteforceUpdate($k);
        }

        if (0 === $passwordLength) {
            $guesses = 1.0;
            $optimalSequence = [];
        } else {
            $optimalSequence = $this->unwind($passwordLength);
            $optimalSequenceLength = \count($optimalSequence);
            $guesses = $this->optimal['g'][$passwordLength - 1][$optimalSequenceLength];
        }

        return new ScorerResult(
            guesses: $guesses,
            guessesLog10: log10($guesses),
            sequence: $optimalSequence,
        );
    }

    /**
     * Considers whether a length-l sequence ending at match m is better (fewer guesses)
     * than previously encountered sequences, updating state if so.
     */
    private function update(MatchInterface $match, int $sequenceLength): void
    {
        $k = $match->end();

        $pi = Options::getClassByPattern($this->options->scorers, $match::getPattern())::getGuesses($match, $this->options);

        if ($sequenceLength > 1) {
            // We're considering a length-l sequence ending with match m:
            // obtain the product term in the minimization function by multiplying m's guesses
            // by the product of the length-(l-1) sequence ending just before m, at m.i - 1.
            $pi *= $this->optimal['pi'][$match->begin() - 1][$sequenceLength - 1];
        }

        // Calculate the minimization func.
        $g = Factorial::fact($sequenceLength) * $pi;
        if (!$this->excludeAdditive) {
            $g += self::MIN_GUESSES_BEFORE_GROWING_SEQUENCE ** ($sequenceLength - 1);
        }

        // Update state if new best.
        // First see if any competing sequences covering this prefix, with l or fewer matches,
        // fare better than this sequence. If so, skip it and return.
        foreach ($this->optimal['g'][$k] as $competingPatternLength => $competingMetricMatch) {
            if ($competingPatternLength > $sequenceLength) {
                continue;
            }
            if ($competingMetricMatch <= $g) {
                return;
            }
        }

        $this->optimal['g'][$k][$sequenceLength] = $g;
        $this->optimal['m'][$k][$sequenceLength] = $match;
        $this->optimal['pi'][$k][$sequenceLength] = $pi;
    }

    /**
     * Evaluates bruteforce matches ending at passwordCharIndex (k).
     */
    private function bruteforceUpdate(int $passwordCharIndex): void
    {
        // See if a single bruteforce match spanning the k-prefix is optimal.
        $match = $this->makeBruteforceMatch(0, $passwordCharIndex);
        $this->update($match, 1);

        // Generate k bruteforce matches, spanning from (i=1, j=k) up to (i=k, j=k).
        // See if adding these new matches to any of the sequences in optimal[i-1]
        // leads to new bests.
        for ($i = 1; $i <= $passwordCharIndex; ++$i) {
            $match = $this->makeBruteforceMatch($i, $passwordCharIndex);
            foreach ($this->optimal['m'][$i - 1] as $sequenceLength => $lastMatch) {
                // Corner: an optimal sequence will never have two adjacent bruteforce matches.
                // It is strictly better to have a single bruteforce match spanning the same region:
                // same contribution to the guess product with a lower length.
                // It is safe to skip those cases.
                if ($lastMatch::getPattern() === BruteforceMatch::getPattern()) {
                    continue;
                }

                $this->update($match, $sequenceLength + 1);
            }
        }
    }

    /**
     * Makes bruteforce match objects spanning begin index to end index, inclusive.
     */
    private function makeBruteforceMatch(int $begin, int $end): BruteforceMatch
    {
        return new BruteforceMatch(
            password: $this->password,
            begin: $begin,
            end: $end,
            token: mb_substr($this->password, $begin, $end - $begin + 1),
        );
    }

    /**
     * Step backwards through optimal.m starting at the end, constructing the final optimal match sequence.
     *
     * @return MatchInterface[]
     */
    private function unwind(int $passwordLength): array
    {
        $optimalMatchSequence = [];
        $k = $passwordLength - 1;

        // Find the final best sequence length and score.
        $sequenceLength = 0;
        $g = \INF;

        foreach ($this->optimal['g'][$k] as $candidateSequenceLength => $candidateMetricMatch) {
            if ($candidateMetricMatch < $g) {
                $sequenceLength = $candidateSequenceLength;
                $g = $candidateMetricMatch;
            }
        }

        while ($k >= 0) {
            /** @var MatchInterface $match */
            $match = $this->optimal['m'][$k][$sequenceLength];
            array_unshift($optimalMatchSequence, $match);
            $k = $match->begin() - 1;
            --$sequenceLength;
        }

        return $optimalMatchSequence;
    }
}
