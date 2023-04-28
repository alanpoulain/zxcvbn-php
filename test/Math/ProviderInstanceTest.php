<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Math;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Math\AbstractProviderInstance;
use ZxcvbnPhp\Math\Implementation\BinomialProviderFloat64;

#[CoversClass(AbstractProviderInstance::class)]
final class ProviderInstanceTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testNoProvider(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No valid providers');

        MockProviderInstance::provider();
    }

    public function testGetProvider(): void
    {
        MockProviderInstance::setPossibleProviderClasses([BinomialProviderFloat64::class => true]);

        self::assertInstanceOf(BinomialProviderFloat64::class, MockProviderInstance::provider());
    }
}
