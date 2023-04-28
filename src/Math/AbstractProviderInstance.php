<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Math;

/**
 * @template P
 */
abstract class AbstractProviderInstance
{
    private static array $providerInstances = [];

    // In order of priority. The first provider with a value of true will be used.
    abstract protected static function getPossibleProviderClasses(): array;

    /**
     * @return class-string<P>[]
     */
    public static function getUsableProviderClasses(): array
    {
        $possibleProviderClasses = array_filter(static::getPossibleProviderClasses());

        return array_keys($possibleProviderClasses);
    }

    /**
     * @return P
     */
    protected static function getProvider()
    {
        $class = static::class;
        if (!isset(self::$providerInstances[$class])) {
            self::$providerInstances[$class] = self::initProvider();
        }

        return self::$providerInstances[$class];
    }

    /**
     * @return P
     */
    private static function initProvider()
    {
        $providerClasses = self::getUsableProviderClasses();

        if (!$providerClasses) {
            throw new \LogicException('No valid providers');
        }

        $bestProviderClass = $providerClasses[0];

        return new $bestProviderClass();
    }
}
