<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\KeyboardLayouts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\KeyboardLayouts\AzertyKeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\BepoKeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\DvorakKeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\KeyboardLayouts;
use ZxcvbnPhp\KeyboardLayouts\KeypadKeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\KeypadMacKeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\NFAzertyKeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\QwertyKeyboardLayout;
use ZxcvbnPhp\KeyboardLayouts\QwertzKeyboardLayout;

#[CoversClass(KeyboardLayouts::class)]
final class KeyboardLayoutsTest extends TestCase
{
    public function testGetKeyboardLayouts(): void
    {
        self::assertSame([
            AzertyKeyboardLayout::class,
            BepoKeyboardLayout::class,
            DvorakKeyboardLayout::class,
            KeypadKeyboardLayout::class,
            KeypadMacKeyboardLayout::class,
            NFAzertyKeyboardLayout::class,
            QwertyKeyboardLayout::class,
            QwertzKeyboardLayout::class,
        ], KeyboardLayouts::getKeyboardLayouts());
    }

    public function testKeyboardLayoutByName(): void
    {
        self::assertSame(QwertyKeyboardLayout::class, KeyboardLayouts::getKeyboardLayoutByName('qwerty'));
    }

    public function testNotFoundKeyboardLayoutByName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Keyboard layout with name abc not found');

        KeyboardLayouts::getKeyboardLayoutByName('abc');
    }
}
