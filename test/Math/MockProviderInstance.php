<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Math;

use ZxcvbnPhp\Math\AbstractProviderInstance;

final class MockProviderInstance extends AbstractProviderInstance
{
    private static array $possibleProviderClasses = [];

    public static function provider()
    {
        return self::getProvider();
    }

    public static function setPossibleProviderClasses(array $possibleProviderClasses): void
    {
        self::$possibleProviderClasses = $possibleProviderClasses;
    }

    protected static function getPossibleProviderClasses(): array
    {
        return self::$possibleProviderClasses;
    }
}
