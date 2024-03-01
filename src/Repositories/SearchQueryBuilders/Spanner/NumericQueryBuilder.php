<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;

class NumericQueryBuilder implements BuilderInterface
{
    /**
     * @param NumericCondition $condition
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $value = $condition->getValue();

        if (SearchComponentConfigHelper::isUseRawQueryStatements() || $condition->isString()) {
            if ($condition->isString()) {
                $column = "SAFE_CAST(task_metadata_value AS INT64)";
            }
            $builder->whereRaw("$column {$condition->getMathematicalOperator()} $value ");
        } else {
            $builder->where($column, $condition->getMathematicalOperator(), $value);
        }
    }
}
