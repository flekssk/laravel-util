<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Properties\Collections;

use FKS\Http\Attributes\Properties\DateTimeProperty;
use FKS\Http\Attributes\Properties\StringProperty;
use FKS\Http\Attributes\Properties\UuidProperty;

class CRUDInfoCollection
{
    public static function createdProperties(): array
    {
        return self::crudInfoProperties('created');
    }

    public static function updatedProperties(): array
    {
        return self::crudInfoProperties('updated');
    }

    private static function crudInfoProperties(string $action): array
    {
        return [
            new DateTimeProperty($action . '_at'),
            new UuidProperty($action . '_by'),
            new StringProperty($action . '_by_name', 'User Name'),
        ];
    }
}
