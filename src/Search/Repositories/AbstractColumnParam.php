<?php

namespace FKS\Search\Repositories;

use Illuminate\Contracts\Database\Query\Expression;
use FKS\Search\Requests\SortingRuleBuilders\Enums\SortAsEnum;

abstract class AbstractColumnParam
{
    abstract function getSortColumn($sortDefinition) : string|Expression;

    public function applySortConditions($builder, $sortDefinition) : void {
        // apply any conditions related to sorting (joins, where's, etc.)
    }

    public function applySort($builder, $sortDefinition) : void
    {
        $sortAs = SortAsEnum::tryFrom($sortDefinition['sort_as'] ?? '');
        $sortColumn = $this->getSortColumn($sortDefinition);

        if ($sortAs !== null && is_string($sortColumn)) {
            $castAs = match ($sortAs) {
                SortAsEnum::INTEGER => 'INT64',
                SortAsEnum::FLOAT => 'FLOAT64',
                SortAsEnum::TIMESTAMP => 'TIMESTAMP',
            };
            $builder->orderByRaw("SAFE_CAST($sortColumn as $castAs) {$sortDefinition['direction']}");
            return;
        }

        $builder->orderBy($sortColumn, $sortDefinition['direction']);
    }
}
