<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

/**
 * @codeCoverageIgnore
 */
final class KeypadMacKeyboardLayout implements KeyboardLayoutInterface
{
    public static function getName(): string
    {
        return 'keypadMac';
    }

    public static function getLayout(): string
    {
        return <<<'EOD'
  = / *
7 8 9 -
4 5 6 +
1 2 3
  0 .
EOD;
    }

    public static function getShiftedCharacters(): ?string
    {
        return null;
    }

    public static function isSlanted(): bool
    {
        return false;
    }
}
