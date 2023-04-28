<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

/**
 * @codeCoverageIgnore
 */
final class QwertzKeyboardLayout implements KeyboardLayoutInterface
{
    public static function getName(): string
    {
        return 'qwertz';
    }

    public static function getLayout(): string
    {
        return <<<'EOD'
^° 1! 2" 3§ 4$ 5% 6& 7/ 8( 9) 0= ß? ´`
    qQ wW eE rR tT zZ uU iI oO pP üÜ +*
     aA sS dD fF gG hH jJ kK lL öÖ äÄ #'
   <> yY xX cC vV bB nN mM ,; .: -_
EOD;
    }

    public static function getShiftedCharacters(): ?string
    {
        return '°!"§$%&/()=?QWERTZUIOPÜ*ASDFGHJKLÖÄ\'>YXCVBNM;:_';
    }

    public static function isSlanted(): bool
    {
        return true;
    }
}
