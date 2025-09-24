<?php

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use FKS\Search\Repositories\ColumnParamMap;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;

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
