<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

/**
 * @codeCoverageIgnore
 */
final class NFAzertyKeyboardLayout implements KeyboardLayoutInterface
{
    public static function getName(): string
    {
        return 'nFAzerty';
    }

    public static function getLayout(): string
    {
        return <<<'EOD'
@# à1 é2 è3 ê4 (5 )6 ‘7 ’8 «9 »0 '" ^¨
    aA zZ eE rR tT yY uU iI oO pP -– +±
     qQ sS dD fF gG hH jJ kK lL mM /\ *½
   <> wW xX cC vV bB nN .? ,! :… ;=
EOD;
    }

    public static function getShiftedCharacters(): ?string
    {
        return '#1234567890"¨AZERYTUIOP–±QSDFGHJKLM\½>WXCVBN?!…=';
    }

    public static function isSlanted(): bool
    {
        return true;
    }
}
