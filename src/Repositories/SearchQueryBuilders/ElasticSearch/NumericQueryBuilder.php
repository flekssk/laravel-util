<?php

namespace FKS\Repositories\SearchQueryBuilders\ElasticSearch;

use Illuminate\Database\Query\Builder;
use InvalidArgumentException;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;

class NumericQueryBuilder implements BuilderInterface
{
    /**
     * @param ElasticSearchQueryBuilder $builder
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $value = $condition->isInteger() ? (int) $condition->getValue() : $condition->getValue();

        switch ($operator = $condition->getMathematicalOperator()) {
            case '=':
                $builder->where($column, $value);
                break;
            case '!=':
                $builder->where($column, $value, $builder::SECTION_MUST_NOT);
                break;
            default:
                $builder->range($column, [$operator => $value]);
        }
    }
}
