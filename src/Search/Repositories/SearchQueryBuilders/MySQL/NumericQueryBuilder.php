<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use FKS\Search\Helpers\SearchComponentConfigHelper;
use FKS\Search\Repositories\ColumnParamMap;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Search\ValueObjects\Conditions\NumericCondition;

class NumericQueryBuilder implements BuilderInterface
{
    /**
     * @param NumericCondition $condition
     */
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, ColumnParamMap|string $column = null): void
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
