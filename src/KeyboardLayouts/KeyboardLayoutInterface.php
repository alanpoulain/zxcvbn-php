<?php

declare(strict_types=1);

namespace ZxcvbnPhp\KeyboardLayouts;

interface KeyboardLayoutInterface
{
    public static function getName(): string;

    public static function getLayout(): string;

    public static function getShiftedCharacters(): ?string;

    public static function isSlanted(): bool;
}
