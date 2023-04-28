<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

/**
 * @codeCoverageIgnore
 */
final class QwertyKeyboardLayout implements KeyboardLayoutInterface
{
    public static function getName(): string
    {
        return 'qwerty';
    }

    public static function getLayout(): string
    {
        return <<<'EOD'
`~ 1! 2@ 3# 4$ 5% 6^ 7& 8* 9( 0) -_ =+
    qQ wW eE rR tT yY uU iI oO pP [{ ]} \|
     aA sS dD fF gG hH jJ kK lL ;: '"
      zZ xX cC vV bB nN mM ,< .> /?
EOD;
    }

    public static function getShiftedCharacters(): ?string
    {
        return '~!@#$%^&*()_+QWERTYUIOP{}|ASDFGHJKL:"ZXCVBNM<>?';
    }

    public static function isSlanted(): bool
    {
        return true;
    }
}
