<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\MySQL;

use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use Illuminate\Database\Query\Builder;

class NullQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, string $column = null): void
    {
        $builder->whereNull($column);
    }
}
