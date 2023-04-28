<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Result\FeedbackResult;
use ZxcvbnPhp\Translation\Translator;

/**
 * Gives some user guidance based on the strength of a password.
 */
final class Feedback
{
    private Options $options;

    public function __construct(?Options $options = null)
    {
        $this->options = $options ?? Configurator::getOptions(new Config());
    }

    /**
     * @param MatchInterface[] $sequence
     */
    public function getFeedback(int $score, array $sequence): FeedbackResult
    {
        $translator = Translator::getTranslator($this->options);

        // Starting feedback.
        if (0 === \count($sequence)) {
            return new FeedbackResult(
                warning: null,
                suggestions: [
                    $translator->trans('suggestions.useWords'),
                    $translator->trans('suggestions.noNeed'),
                ],
            );
        }

        // No feedback if score is good or great.
        if ($score > 2) {
            return new FeedbackResult(
                warning: null,
                suggestions: [],
            );
        }

        // Tie feedback to the longest match for longer sequences.
        $longestMatch = $this->getLongestMatch($sequence);

        /** @var FeedbackResult $feedback */
        $feedback = Options::getClassByPattern($this->options->feedbacks, $longestMatch::getPattern())::getFeedback($longestMatch, $this->options, 1 === \count($sequence));
        $suggestions = $feedback->suggestions;
        $extraSuggestion = $translator->trans('suggestions.anotherWord');

        array_unshift($suggestions, $extraSuggestion);
        $translatedSuggestions = [];
        foreach ($suggestions as $suggestion) {
            $translatedSuggestions[] = $translator->trans($suggestion);
        }

        return new FeedbackResult(
            warning: null === $feedback->warning ? null : $translator->trans($feedback->warning),
            suggestions: $translatedSuggestions,
        );
    }

    /**
     * @param MatchInterface[] $sequence
     */
    private function getLongestMatch(array $sequence): MatchInterface
    {
        $longestMatch = $sequence[0];
        foreach (\array_slice($sequence, 1) as $match) {
            if (mb_strlen($match->token()) > mb_strlen($longestMatch->token())) {
                $longestMatch = $match;
            }
        }

        return $longestMatch;
    }
}
