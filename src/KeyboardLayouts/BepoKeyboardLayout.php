<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

/**
 * @codeCoverageIgnore
 */
final class BepoKeyboardLayout implements KeyboardLayoutInterface
{
    public static function getName(): string
    {
        return 'bepo';
    }

    public static function getLayout(): string
    {
        return <<<'EOD'
$# "1 «2 »3 (4 )5 @6 +7 -8 /9 *0 =° %`
    bB éÉ pP oO èÈ ^! vV dD lL jJ zZ wW
     aA uU iI eE ,; cC tT sS rR nN mM çÇ
   êÊ àÀ yY xX .: kK ’? qQ gG hH fF
EOD;
    }

    public static function getShiftedCharacters(): ?string
    {
        return '#1234567890°`BÉPOÈ!VDLJZWAUIE;CTSRNMÇÊÀYX:K?QGHF';
    }

    public static function isSlanted(): bool
    {
        return true;
    }
}
