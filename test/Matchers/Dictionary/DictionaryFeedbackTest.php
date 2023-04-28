<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Dictionary;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Bruteforce\BruteforceMatch;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryFeedback;
use ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch;
use ZxcvbnPhp\Result\FeedbackResult;

#[CoversClass(DictionaryFeedback::class)]
final class DictionaryFeedbackTest extends TestCase
{
    public function testFeedbackTop10Password(): void
    {
        $feedback = $this->getFeedbackForToken('password', 'common-passwords', 10, soleMatch: true);

        self::assertSame(
            'warnings.topTen',
            $feedback->warning,
            'dictionary match warns about top-10 password'
        );
    }

    public function testFeedbackTop100Password(): void
    {
        $feedback = $this->getFeedbackForToken('hunter', 'common-passwords', 100, soleMatch: true);

        self::assertSame(
            'warnings.topHundred',
            $feedback->warning,
            'dictionary match warns about top-100 password'
        );
    }

    public function testFeedbackTopPasswordSoleMatch(): void
    {
        $feedback = $this->getFeedbackForToken('mytruck', 'common-passwords', 19324, soleMatch: true);

        self::assertSame(
            'warnings.common',
            $feedback->warning,
            'dictionary match warns about common password'
        );
    }

    public function testFeedbackTopPasswordNotSoleMatch(): void
    {
        $feedback = $this->getFeedbackForToken('browndog', 'common-passwords', 10000, soleMatch: false);

        self::assertSame(
            'warnings.similarToCommon',
            $feedback->warning,
            'dictionary match warns about common password (not a sole match)'
        );
    }

    public function testFeedbackTopPasswordNotSoleMatchRankTooLow(): void
    {
        $feedback = $this->getFeedbackForToken('mytruck', 'common-passwords', 19324, soleMatch: false);

        self::assertNull(
            $feedback->warning,
            'no warning for a non-sole match in the password dictionary'
        );
    }

    public function testFeedbackWikipediaWordSoleMatch(): void
    {
        $feedback = $this->getFeedbackForToken('university', 'en-wikipedia', 69, soleMatch: true);

        self::assertSame(
            'warnings.wordByItself',
            $feedback->warning,
            'dictionary match warns about Wikipedia word (sole match)'
        );
    }

    public function testFeedbackWikipediaWordNonSoleMatch(): void
    {
        $feedback = $this->getFeedbackForToken('university', 'en-wikipedia', 69, soleMatch: false);

        self::assertNull(
            $feedback->warning,
            "dictionary match doesn't warn about Wikipedia word (not a sole match)"
        );
    }

    public function testFeedbackNameSoleMatch(): void
    {
        $feedback = $this->getFeedbackForToken('carlos', 'es-es-maleFirstnames', 21, soleMatch: true);

        self::assertSame(
            'warnings.namesByThemselves',
            $feedback->warning,
            'dictionary match warns about surname (sole match)'
        );
    }

    public function testFeedbackNameNonSoleMatch(): void
    {
        $feedback = $this->getFeedbackForToken('janowska', 'pl-femaleLastnames', 21, soleMatch: false);

        self::assertSame(
            'warnings.commonNames',
            $feedback->warning,
            'dictionary match warns about surname (not a sole match)'
        );
    }

    public function testFeedbackUserInputs(): void
    {
        $feedback = $this->getFeedbackForToken('my-website', 'userInputs', 3, soleMatch: true);

        self::assertSame(
            'warnings.userInputs',
            $feedback->warning,
            'dictionary match warns about user inputs'
        );
    }

    public function testFeedbackTvAndFilmDictionary(): void
    {
        $feedback = $this->getFeedbackForToken('know', 'us_tv_and_film', 9, soleMatch: true);

        self::assertNull(
            $feedback->warning,
            'no warning for match from us_tv_and_film dictionary'
        );
    }

    public function testFeedbackAllUppercaseWord(): void
    {
        $feedback = $this->getFeedbackForToken('PASSWORD', 'common-passwords', 2, soleMatch: true);

        self::assertContains(
            'suggestions.allUppercase',
            $feedback->suggestions,
            'dictionary match gives suggestion for all-uppercase word'
        );
    }

    public function testFeedbackWordStartsWithUppercase(): void
    {
        $feedback = $this->getFeedbackForToken('Password', 'common-passwords', 2, soleMatch: true);

        self::assertContains(
            'suggestions.capitalization',
            $feedback->suggestions,
            'dictionary match gives suggestion for word starting with uppercase'
        );
    }

    public function testFeedbackReversed(): void
    {
        $feedback = $this->getFeedbackForToken('looc', 'en-wikipedia', 69, soleMatch: true, reversed: true);

        self::assertSame(
            'warnings.wordByItself',
            $feedback->warning,
            "reverse dictionary match didn't lose the original dictionary match warning"
        );
        self::assertContains(
            'suggestions.reverseWords',
            $feedback->suggestions,
            'reverse dictionary match gives correct suggestion'
        );
    }

    public function testFeedbackReversedTop100Password(): void
    {
        $feedback = $this->getFeedbackForToken('retunh', 'common-passwords', 37, soleMatch: true, reversed: true);

        self::assertSame(
            'warnings.similarToCommon',
            $feedback->warning,
            "reverse dictionary match doesn't give top-100 warning"
        );
    }

    public function testFeedbackReversedShortToken(): void
    {
        $feedback = $this->getFeedbackForToken('šán', 'cs-wikipedia', 1, soleMatch: true, reversed: true);

        self::assertSame(
            'warnings.wordByItself',
            $feedback->warning,
            'reverse dictionary match still gives warning for short token'
        );
        self::assertNotContains(
            'suggestions.reverseWords',
            $feedback->suggestions,
            "reverse dictionary match doesn't give suggestion for short token"
        );
    }

    public function testFeedbackL33t(): void
    {
        $feedback = $this->getFeedbackForToken('univer5ity', 'en-wikipedia', 69, soleMatch: true, l33t: true);

        self::assertSame(
            'warnings.wordByItself',
            $feedback->warning,
            "l33t match didn't lose the original dictionary match warning"
        );
        self::assertContains(
            'suggestions.l33t',
            $feedback->suggestions,
            'l33t match gives correct suggestion'
        );
    }

    public function testFeedbackL33tTop100Password(): void
    {
        $feedback = $this->getFeedbackForToken('hunt3r', 'common-passwords', 37, soleMatch: true, l33t: true);

        self::assertSame(
            'warnings.similarToCommon',
            $feedback->warning,
            "l33t match doesn't give top-100 warning"
        );
    }

    private function getFeedbackForToken(string $token, string $dictionary, int $rank, bool $soleMatch, bool $reversed = false, bool $l33t = false): FeedbackResult
    {
        $match = new DictionaryMatch(
            password: $token,
            begin: 0,
            end: \strlen($token) - 1,
            token: $token,
            matchedWord: $token,
            rank: $rank,
            dictionaryName: $dictionary,
            reversed: $reversed,
            l33t: $l33t,
            levenshteinDistance: -1
        );

        return DictionaryFeedback::getFeedback($match, Configurator::getOptions(new Config()), $soleMatch);
    }

    public function testInvalidMatch(): void
    {
        $this->expectExceptionMessage('Match object needs to be of class ZxcvbnPhp\Matchers\Dictionary\DictionaryMatch');

        DictionaryFeedback::getFeedback(new BruteforceMatch(
            password: 'pass',
            begin: 0,
            end: 3,
            token: 'pass',
        ), Configurator::getOptions(new Config()));
    }

    public function testGetPattern(): void
    {
        self::assertSame('dictionary', DictionaryFeedback::getPattern());
    }
}
