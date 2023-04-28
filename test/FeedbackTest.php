<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Feedback;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Date\DateMatch;
use ZxcvbnPhp\Matchers\Sequence\SequenceMatch;
use ZxcvbnPhp\Result\FeedbackResult;
use ZxcvbnPhp\Test\Matchers\MockMatch;

#[CoversClass(Feedback::class)]
#[CoversClass(FeedbackResult::class)]
final class FeedbackTest extends TestCase
{
    private Feedback $feedback;

    protected function setUp(): void
    {
        $this->feedback = new Feedback();
    }

    public function testFeedbackForEmptyPassword(): void
    {
        $feedback = $this->feedback->getFeedback(0, []);

        self::assertNull($feedback->warning, 'default warning');
        self::assertContains(
            'Use multiple words, but avoid common phrases.',
            $feedback->suggestions,
            'default suggestion #1'
        );
        self::assertContains(
            'You can create strong passwords without using symbols, numbers, or uppercase letters.',
            $feedback->suggestions,
            'default suggestion #2'
        );
    }

    public function testHighScoringSequence(): void
    {
        $match = new BruteforceMatch(password: 'a', begin: 0, end: 1, token: 'a');
        $feedback = $this->feedback->getFeedback(3, [$match]);

        self::assertNull($feedback->warning, 'no warning for good score');
        self::assertEmpty($feedback->suggestions, 'no suggestions for good score');
    }

    public function testLongestMatchGetsFeedback(): void
    {
        $match1 = new SequenceMatch(password: 'abcd26-01-1991', begin: 0, end: 4, token: 'abcd', sequenceName: 'lower', sequenceSpace: 2155, ascending: true);
        $match2 = new DateMatch(password: 'abcd26-01-1991', begin: 4, end: 14, token: '26-01-1991', day: 26, month: 1, year: 1991, separator: '-');
        $feedback = $this->feedback->getFeedback(1, [$match1, $match2]);

        self::assertSame(
            'Dates are easy to guess.',
            $feedback->warning,
            'warning provided for the longest match'
        );
        self::assertContains(
            'Avoid dates and years that are associated with you.',
            $feedback->suggestions,
            'suggestion provided for the longest match'
        );
        self::assertNotContains(
            'Avoid common character sequences.',
            $feedback->suggestions,
            'no suggestion provided for the shorter match'
        );
    }

    public function testDefaultSuggestion(): void
    {
        $match = new DateMatch(password: '26-01-1991', begin: 0, end: 10, token: '26-01-1991', day: 26, month: 1, year: 1991, separator: '-');
        $feedback = $this->feedback->getFeedback(1, [$match]);

        self::assertContains(
            'Add more words that are less common.',
            $feedback->suggestions,
            'default suggestion provided'
        );
        self::assertCount(2, $feedback->suggestions, "default suggestion doesn't override existing suggestion");
    }

    public function testBruteforceFeedback(): void
    {
        $match = new BruteforceMatch(password: 'qkcriv', begin: 0, end: 6, token: 'qkcriv');
        $feedback = $this->feedback->getFeedback(1, [$match]);

        self::assertNull($feedback->warning, 'bruteforce match has no warning');
        self::assertSame(
            ['Add more words that are less common.'],
            $feedback->suggestions,
            'bruteforce match only has the default suggestion'
        );
    }

    public function testNotTranslatedFeedback(): void
    {
        $feedback = new Feedback(Configurator::getOptions(new Config(translationEnabled: false)));
        $match = new DateMatch(password: '26-01-1991', begin: 0, end: 10, token: '26-01-1991', day: 26, month: 1, year: 1991, separator: '-');
        $feedbackResult = $feedback->getFeedback(1, [$match]);

        self::assertSame(
            'warnings.dates',
            $feedbackResult->warning,
            'warning not translated'
        );
        self::assertSame(
            ['suggestions.anotherWord', 'suggestions.dates'],
            $feedbackResult->suggestions,
            'suggestions not translated'
        );
    }

    public function testFrenchTranslatedFeedback(): void
    {
        $feedback = new Feedback(Configurator::getOptions(new Config(translationLocale: 'fr')));
        $match = new DateMatch(password: '26-01-1991', begin: 0, end: 10, token: '26-01-1991', day: 26, month: 1, year: 1991, separator: '-');
        $feedbackResult = $feedback->getFeedback(1, [$match]);

        self::assertSame(
            'Les dates sont faciles à deviner.',
            $feedbackResult->warning,
            'warning translated in French'
        );
        self::assertSame(
            ['Ajoutez des mots moins courants.', 'Évitez les dates et les années qui vous sont associées (ex : date ou année de naissance).'],
            $feedbackResult->suggestions,
            'suggestions translated in French'
        );
    }

    public function testNotFoundFeedback(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Class with pattern mock not found');

        $this->feedback->getFeedback(1, [new MockMatch(0, 5, 1)]);
    }
}
