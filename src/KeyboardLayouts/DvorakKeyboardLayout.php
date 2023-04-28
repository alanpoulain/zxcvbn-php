<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

/**
 * @codeCoverageIgnore
 */
final class DvorakKeyboardLayout implements KeyboardLayoutInterface
{
    public static function getName(): string
    {
        return 'dvorak';
    }

    public static function getLayout(): string
    {
        return <<<'EOD'
`~ 1! 2@ 3# 4$ 5% 6^ 7& 8* 9( 0) [{ ]}
    '" ,< .> pP yY fF gG cC rR lL /? =+ \|
     aA oO eE uU iI dD hH tT nN sS -_
      ;: qQ jJ kK xX bB mM wW vV zZ
EOD;
    }

    public static function getShiftedCharacters(): ?string
    {
        return '~!@#$%^&*(){}"<>PYFGCRL?+|AOEUIDHTNS_:QJKXBMWVZ';
    }

    public static function isSlanted(): bool
    {
        return true;
    }
}
