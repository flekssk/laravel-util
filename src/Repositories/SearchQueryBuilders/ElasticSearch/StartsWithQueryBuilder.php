<?php

namespace FKS\Repositories\SearchQueryBuilders\ElasticSearch;

use Illuminate\Database\Query\Builder;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;

class StartsWithQueryBuilder implements BuilderInterface
{
    /**
     * @param ElasticSearchQueryBuilder $builder
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        if ($condition->getValue()) {
            $builder->prefix($column, $condition->getValue());
        } else {
            $builder->whereNull($column);
        }
    }
}
