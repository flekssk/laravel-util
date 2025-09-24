<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use FKS\Search\Helpers\SearchComponentConfigHelper;
use FKS\Search\Repositories\ColumnParamMap;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Search\ValueObjects\Conditions\EqualsCondition;

class EqualsQueryBuilder implements BuilderInterface
{
    /**
     * @param EqualsCondition $condition
     */
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, ColumnParamMap|string $column = null): void
    {
        $isRawQueryExpected = SearchComponentConfigHelper::isUseRawQueryStatements();

        if ($column instanceof ColumnParamMap) {
            $filterParamParts = explode('.', $condition->getFilterParam());
            if ($isRawQueryExpected) {
                $builder->whereRaw("$column->tableName.$column->tableColumn = '$filterParamParts[1]'");
            } else {
                $builder->where($column->tableName . '.' . $column->tableColumn, $filterParamParts[1]);
            }
            $column = $column->tableName . '.' . $column->tableValuesColumn;
        }

        if ($condition->getData() === null) {
            $builder->whereNull($column);
        } elseif ($isRawQueryExpected) {
            $builder->whereRaw("$column = {$condition->getData()}");
        } else {
            $builder->where($column, $condition->getData());
        }
    }
}
