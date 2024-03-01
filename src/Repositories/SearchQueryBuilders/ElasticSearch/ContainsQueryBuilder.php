<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\ElasticSearch;

use FKS\Repositories\ColumnParamMap;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;
use FKS\ValueObjects\Id;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;

class ContainsQueryBuilder extends \FKS\Repositories\SearchQueryBuilders\Spanner\ContainsQueryBuilder
{
    /**
     * @param ElasticSearchQueryBuilder $builder
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $values = $this->prepareValues($condition);
        $filterHasNullValue = in_array(null, $condition->getValues(), true);
        if (empty($values) && !$filterHasNullValue) {
            return;
        }

        if ($condition->isBytes()) {
            $values = Id::mapArrayValueToUuid($values);
        }

        if ($column instanceof ColumnParamMap) {
            $column = $column->tableColumn;
        }
        if ($condition->isContains()) {
            if ($filterHasNullValue && $values) {
                $builder->where(function (ElasticSearchQueryBuilder $builder) use ($column, $values) {
                    $builder->whereIn($column, $values, $builder::SECTION_SHOULD);
                    $builder->whereNull($column, $builder::SECTION_SHOULD);
                });
            } elseif ($values) {
                $builder->whereIn($column, $values);
            } else {
                $builder->whereNull($column);
            }
        } else {
            if ($filterHasNullValue && $values) {
                $builder->where(function (ElasticSearchQueryBuilder $builder) use ($column, $values) {
                    $builder->whereNotNull($column, $builder::SECTION_SHOULD);
                    $builder->whereNotIn($column, $values, $builder::SECTION_SHOULD);
                });
            } elseif ($values) {
                $builder->whereNotIn($column, $values);
            } else {
                $builder->whereNotNull($column);
            }
        }
    }
}
