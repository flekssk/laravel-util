<?php

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;

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
