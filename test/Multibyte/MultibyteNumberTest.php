<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Multibyte;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Multibyte\MultibyteNumber;

#[CoversClass(MultibyteNumber::class)]
final class MultibyteNumberTest extends TestCase
{
    public function testConvertClassicNumberToInteger(): void
    {
        self::assertSame(2024, MultibyteNumber::convertToInteger('2024'));
    }

    #[RequiresPhpExtension('intl')]
    public function testConvertThaiNumberToInteger(): void
    {
        self::assertSame(7802, MultibyteNumber::convertToInteger('๗๘๐๒'));
    }

    public function testConvertToIntegerNoIntl(): void
    {
        if (\extension_loaded('intl')) {
            self::markTestSkipped();
        }

        self::assertSame(-1, MultibyteNumber::convertToInteger('๗๘๐๒'));
    }
}
