<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders;

use Illuminate\Database\Eloquent\Builder;

interface BuilderInterface
{
    public function applyCondition(Builder $builder, $condition, string $column = null): void;
}
