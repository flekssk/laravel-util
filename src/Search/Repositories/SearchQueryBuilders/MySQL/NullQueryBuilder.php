<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;

class NullQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, string $column = null): void
    {
        $builder->whereNull($column);
    }
}
