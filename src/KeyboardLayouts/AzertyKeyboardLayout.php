<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

/**
 * @codeCoverageIgnore
 */
final class AzertyKeyboardLayout implements KeyboardLayoutInterface
{
    public static function getName(): string
    {
        return 'azerty';
    }

    public static function getLayout(): string
    {
        return <<<'EOD'
²~ &1 é2 "3 '4 (5 -6 è7 _8 ç9 à0 )° +=
    aA zZ eE rR tT yY uU iI oO pP ^" $£
     qQ sS dD fF gG hH jJ kK lL mM ù% *µ
   <> wW xX cC vV bB nN ,? ;. :/ !§
EOD;
    }

    public static function getShiftedCharacters(): ?string
    {
        return '~1234567890°+AZERYTUIOP¨£QSDFGHJKLM%µ>WXCVBN?/.§';
    }

    public static function isSlanted(): bool
    {
        return true;
    }
}
