<?php

namespace FKS\Repositories\SearchQueryBuilders\SQL;

use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use Illuminate\Database\Query\Builder;

class StartsWithQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder $builder, $condition, ColumnParamMap|string $column = null): void
    {
        if ($condition->getValue()) {
            $builder->whereRaw('STARTS_WITH(' . $builder->getGrammar()->wrap($column) . ', ?)', [$condition->getValue()]);
        } else {
            $builder->whereNull($column);
        }
    }
}
