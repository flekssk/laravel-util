<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders;

use Illuminate\Database\Query\Builder;

interface BuilderInterface
{
    public function applyCondition(Builder $builder, $condition, string $column = null): void;
}
