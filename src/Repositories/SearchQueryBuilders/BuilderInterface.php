<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders;

use Illuminate\Database\Query\Builder;

interface BuilderInterface
{
    /**
     * @param Builder $builder
     */
    public function applyCondition($builder, $condition, string $column = null): void;
}
