<?php

namespace FKS\Repositories\SearchQueryBuilders\MySQL;

use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use Illuminate\Database\Query\Builder;

class StartsWithQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, ColumnParamMap|string $column = null): void
    {
        if ($condition->getValue()) {
            $builder->whereRaw('STARTS_WITH(' . $builder->getGrammar()->wrap($column) . ', ?)', [$condition->getValue()]);
        } else {
            $builder->whereNull($column);
        }
    }
}
