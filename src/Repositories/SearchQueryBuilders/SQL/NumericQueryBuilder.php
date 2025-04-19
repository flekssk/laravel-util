<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\SQL;

use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;
use Illuminate\Database\Query\Builder;

class NumericQueryBuilder implements BuilderInterface
{
    /**
     * @param NumericCondition $condition
     */
    public function applyCondition(Builder $builder, $condition, ColumnParamMap|string $column = null): void
    {
        $value = $condition->getValue();

        if ($column instanceof ColumnParamMap) {
            $column = "$column->tableName.$column->tableColumn";
        }

        if (SearchComponentConfigHelper::isUseRawQueryStatements() || $condition->isString()) {
            $builder->whereRaw("$column {$condition->getMathematicalOperator()} $value ");
        } else {
            $builder->where($column, $condition->getMathematicalOperator(), $value);
        }
    }
}
