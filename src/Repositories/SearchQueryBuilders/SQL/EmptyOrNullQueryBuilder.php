<?php

namespace FKS\Repositories\SearchQueryBuilders\SQL;

use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use Illuminate\Database\Query\Builder;

class EmptyOrNullQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder $builder, $condition, string $column = null): void
    {
        $builder->where(function($builder) use ($column) {
            $builder->whereNull($column)
                ->orWhere($column, '=', '');
        });

    }

}
