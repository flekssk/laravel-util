<?php

declare(strict_types=1);

namespace FKS\Factories;

use FKS\Repositories\AbstractColumnParam;
use FKS\Repositories\ColumnParamString;

class ColumnParamFactory
{
    public static function create(AbstractColumnParam|string $sortableField) : AbstractColumnParam
    {
        if (is_string($sortableField)) {
            return new ColumnParamString($sortableField);
        }

        return $sortableField;
    }
}
