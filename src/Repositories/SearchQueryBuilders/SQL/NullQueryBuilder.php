<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use FKS\Repositories\SearchQueryBuilders\BuilderInterface;

class NullQueryBuilder implements BuilderInterface
{
    public function applyCondition($builder, $condition, string $column = null): void
    {
        $builder->whereNull($column);
    }
}
