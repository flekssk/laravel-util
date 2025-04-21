<?php

namespace FKS\Repositories\SearchQueryBuilders\MySQL;

use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use Illuminate\Database\Query\Builder;

class EmptyOrNullQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, string $column = null): void
    {
        $builder->where(function($builder) use ($column) {
            $builder->whereNull($column)
                ->orWhere($column, '=', '');
        });

    }

}
