<?php

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use FKS\Search\Helpers\SearchComponentConfigHelper;
use FKS\Search\Repositories\ColumnParamMap;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Search\ValueObjects\Conditions\DateRangeCondition;

class DateRangeQueryBuilder implements BuilderInterface
{
    /**
     * @param DateRangeCondition $condition
     */
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, ColumnParamMap|string $column = null): void
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

        if ($isRawQueryExpected || $condition->castColumn) {
            if ($condition->isTimestamp()) {
                $from = $from->format('Y-m-d H:i:s e');
                $to = $to->format('Y-m-d H:i:s e');
            }
            if ($condition->castColumn) {
                $castAs = $condition->isTimestamp() ? 'TIMESTAMP' : 'DATE';
                if ($condition->castAsUTC && $condition->isTimestamp()) {
                    $column = "CONCAT($column, ' UTC')";
                }
                $column = "SAFE_CAST($column AS $castAs)";
            }

            $builder->whereRaw("$column BETWEEN '$from' AND '$to'");
        } else {
            $builder->whereBetween($column, [$from, $to]);
        }
    }
}
