<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Configuration\Configurator;

/**
 * The main entry point.
 */
final class Zxcvbn
{
    private readonly Options $options;
    private readonly Matcher $matcher;
    private readonly Scorer $scorer;
    private readonly TimeEstimator $timeEstimator;
    private readonly Feedback $feedback;

    public function __construct(Config $config = new Config())
    {
        $this->options = Configurator::getOptions($config);

        $this->matcher = new Matcher($this->options);
        $this->scorer = new Scorer($this->options);
        $this->feedback = new Feedback($this->options);
        $this->timeEstimator = new TimeEstimator($this->options);
    }

    /**
     * Calculate password strength via non-overlapping minimum entropy patterns.
     *
     * @param string   $password   password to measure
     * @param string[] $userInputs optional user inputs
     */
    public function passwordStrength(#[\SensitiveParameter] string $password, array $userInputs = []): Result
    {
        $timeStart = 0.;
        if ($this->options->calcTimeEnabled) {
            $timeStart = microtime(true);
        }

        $matches = $this->matcher->getMatches($password, $userInputs);

        $matchSequence = $this->scorer->getMostGuessableMatchSequence($password, $matches);
        $attackTimes = $this->timeEstimator->estimateAttackTimes($matchSequence->guesses);
        $feedback = $this->feedback->getFeedback($attackTimes->score, $matchSequence->sequence);

        return new Result(
            password: $password,
            guesses: $matchSequence->guesses,
            guessesLog10: $matchSequence->guessesLog10,
            sequence: $matchSequence->sequence,
            crackTimesSeconds: $attackTimes->crackTimesSeconds,
            crackTimesDisplay: $attackTimes->crackTimesDisplay,
            score: $attackTimes->score,
            feedback: $feedback,
            calcTime: $this->options->calcTimeEnabled ? microtime(true) - $timeStart : -1,
        );
    }
}
