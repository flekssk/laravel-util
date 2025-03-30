<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\ValueObjects\SearchConditions\Conditions\EqualsCondition;

class EqualsQueryBuilder implements BuilderInterface
{
    /**
     * @param EqualsCondition $condition
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
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