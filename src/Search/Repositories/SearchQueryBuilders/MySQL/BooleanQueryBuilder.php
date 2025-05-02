<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use FKS\Search\Helpers\SearchComponentConfigHelper;
use FKS\Search\Repositories\ColumnParamMap;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;

class BooleanQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, ColumnParamMap|string $column = null): void
    {
        $isRawQueryExpected = SearchComponentConfigHelper::isUseRawQueryStatements();
        if ($condition->getValue()) {
            if ($isRawQueryExpected) {
                $value = (bool) $condition->getValue();
                $value = $value ? 'true' : 'false';
                $builder->whereRaw("$column = $value");
            } else {
                $builder->where($column, (bool) $condition->getValue());
            }
        } else {
            $builder->where(static function ($builder) use ($column, $isRawQueryExpected) {
                if ($isRawQueryExpected) {
                    $builder->whereRaw("$column = false")
                        ->orWhereNull($column);
                } else {
                    $builder->where($column, false)
                        ->orWhere($column, null);
                }
            });
        }
    }
}
