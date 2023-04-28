<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Matchers\Separator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\Separator\SeparatorFeedback;
use ZxcvbnPhp\Matchers\Separator\SeparatorMatch;
use ZxcvbnPhp\Result\FeedbackResult;

#[CoversClass(SeparatorFeedback::class)]
final class SeparatorFeedbackTest extends TestCase
{
    public function testFeedback(): void
    {
        $match = new SeparatorMatch(
            password: 'one_two_three',
            begin: 3,
            end: 3,
            token: '_'
        );

        self::assertEquals(new FeedbackResult(), SeparatorFeedback::getFeedback($match, Configurator::getOptions(new Config())));
    }

    public function testGetPattern(): void
    {
        self::assertSame('separator', SeparatorFeedback::getPattern());
    }
}
