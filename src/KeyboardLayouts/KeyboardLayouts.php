<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

final class KeyboardLayouts
{
    /**
     * @return class-string<KeyboardLayoutInterface>[]
     */
    public static function getKeyboardLayouts(): array
    {
        return [
            AzertyKeyboardLayout::class,
            BepoKeyboardLayout::class,
            DvorakKeyboardLayout::class,
            KeypadKeyboardLayout::class,
            KeypadMacKeyboardLayout::class,
            NFAzertyKeyboardLayout::class,
            QwertyKeyboardLayout::class,
            QwertzKeyboardLayout::class,
        ];
    }

    /**
     * @return class-string<KeyboardLayoutInterface>
     */
    public static function getKeyboardLayoutByName(string $name): string
    {
        foreach (self::getKeyboardLayouts() as $keyboardLayout) {
            if ($keyboardLayout::getName() === $name) {
                return $keyboardLayout;
            }
        }

        throw new \RuntimeException(sprintf('Keyboard layout with name %s not found', $name));
    }
}
