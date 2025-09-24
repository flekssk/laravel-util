<?php

namespace FKS\Enums;

use BackedEnum;
use Exception;

trait MappedEnum
{
    public static function values(): array
    {
        if (!is_a(static::class, BackedEnum::class, true)) {
            throw new Exception("EnumMapper can be used only by BackendEnum::class");
        }

        return array_column(static::cases(), 'value');
    }
}