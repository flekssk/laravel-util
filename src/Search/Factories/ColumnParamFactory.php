<?php

declare(strict_types=1);

namespace FKS\Search\Factories;

use Illuminate\Database\Query\Expression;
use FKS\Search\Repositories\AbstractColumnParam;
use FKS\Search\Repositories\ColumnParamString;

class ColumnParamFactory
{
    public static function create(AbstractColumnParam|Expression|string $sortableField) : AbstractColumnParam
    {
        if (
            is_string($sortableField)
            || is_a($sortableField, Expression::class)
        ) {
            return new ColumnParamString($sortableField);
        }

        return $sortableField;
    }
}
