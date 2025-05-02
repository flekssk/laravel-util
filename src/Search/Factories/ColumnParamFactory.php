<?php

declare(strict_types=1);

namespace FKS\Search\Factories;

use FKS\Search\Repositories\AbstractColumnParam;
use FKS\Search\Repositories\ColumnParamString;

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
