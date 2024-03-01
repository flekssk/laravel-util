<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\ElasticSearch;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use FKS\Enums\SearchComponent\SearchCasesEnum;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;

class SearchQueryBuilder implements BuilderInterface
{
    /**
     * @param ElasticSearchQueryBuilder $builder
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $builder->match($column, $condition->value);
    }
}
