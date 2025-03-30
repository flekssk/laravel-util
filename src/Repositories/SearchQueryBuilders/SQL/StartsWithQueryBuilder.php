<?php

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;

class StartsWithQueryBuilder implements BuilderInterface
{
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        if ($condition->getValue()) {
            $builder->whereRaw('STARTS_WITH(' . $builder->getGrammar()->wrap($column) . ', ?)', [$condition->getValue()]);
        } else {
            $builder->whereNull($column);
        }
    }
}
