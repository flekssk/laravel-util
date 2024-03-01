<?php

namespace FKS\Repositories\SearchQueryBuilders\ElasticSearch;

use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;
use FKS\ValueObjects\SearchConditions\Conditions\BooleanCondition;

class BooleanQueryBuilder implements BuilderInterface
{
    /**
     * @param ElasticSearchQueryBuilder $builder
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        if ($condition->getValue()) {
            $builder->where($column, $condition->getValue());
        } elseif ($condition->getValue() === false) {
            $builder->where(function (ElasticSearchQueryBuilder $builder) use ($column) {
                $builder->where($column, false, $builder::SECTION_SHOULD);
                $builder->whereNull($column, $builder::SECTION_SHOULD);
            });
        }
    }
}
