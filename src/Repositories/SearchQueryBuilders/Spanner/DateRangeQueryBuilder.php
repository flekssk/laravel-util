<?php

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;

class DateRangeQueryBuilder implements BuilderInterface
{
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $isRawQueryExpected = SearchComponentConfigHelper::isUseRawQueryStatements();

        $from = $condition->getFrom();
        $to = $condition->getTo();
        if ($condition->isDate()) {
            $from = $from->format('Y-m-d');
            $to = $to->format('Y-m-d');
        }

        if ($column instanceof ColumnParamMap) {
            $filterParamParts = explode('.', $condition->getFilterParam());
            if ($isRawQueryExpected) {
                $builder->whereRaw("$column->tableName.$column->tableColumn = '$filterParamParts[1]'");
            } else {
                $builder->where($column->tableName . '.' . $column->tableColumn, $filterParamParts[1]);
            }
            $column = $column->tableName . '.' . $column->tableValuesColumn;
        }

        if ($isRawQueryExpected) {
            if ($condition->isTimestamp()) {
                $from = $from->format('Y-m-d H:i:s e');
                $to = $to->format('Y-m-d H:i:s e');
            }
            $builder->whereRaw("$column BETWEEN '$from' AND '$to'");
        } else {
            $builder->whereBetween($column, [$from, $to]);
        }
    }
}
