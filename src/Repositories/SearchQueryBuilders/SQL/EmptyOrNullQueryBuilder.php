<?php

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use FKS\Repositories\SearchQueryBuilders\BuilderInterface;

class EmptyOrNullQueryBuilder implements BuilderInterface
{
    public function applyCondition($builder, $condition, string $column = null): void
    {
        $builder->where(function($builder) use ($column) {
            $builder->whereNull($column)
                ->orWhere($column, '=', '');
        });

    }

}
