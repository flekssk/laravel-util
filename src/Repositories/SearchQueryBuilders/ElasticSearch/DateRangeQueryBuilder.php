<?php

namespace FKS\Repositories\SearchQueryBuilders\ElasticSearch;

use Illuminate\Database\Eloquent\Builder;
use FKS\Http\Requests\RuleBuilders\TimestampRangeRuleBuilder;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;

class DateRangeQueryBuilder implements BuilderInterface
{
    /**
     * @param $builder ElasticSearchQueryBuilder
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $from = $condition->getFrom();
        $to = $condition->getTo();
        if ($condition->isDate()) {
            $from = $from->format('Y-m-d');
            $to = $to->format('Y-m-d');
        } else {
            $from = $from->format(TimestampRangeRuleBuilder::FORMAT);
            $to = $to->format(TimestampRangeRuleBuilder::FORMAT);
        }

        if ($column instanceof ColumnParamMap) {
            $column = $column->tableColumn;
        }

        $builder->whereBetween($column, [$from, $to]);
    }
}
