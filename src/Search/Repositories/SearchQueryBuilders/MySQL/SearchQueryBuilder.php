<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use FKS\Search\Enums\SearchCasesEnum;
use FKS\Search\Repositories\ColumnParamMap;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;

class SearchQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder|\Illuminate\Database\Eloquent\Builder $builder, $condition, ColumnParamMap|string $column = null): void
    {
        $cases = is_array($condition->case) ? $condition->case : [$condition->case];
        $value = $condition->value;
        if (blank($value)) {
            $builder->where(function ($query) use ($column) {
                $query->whereNull($column);
                $query->orWhere($column,'=',  '');
            });
            return;
        }
        $databaseColumn = $column;
        if (in_array(SearchCasesEnum::CAST_TO_LOWER, $cases, true)) {
            $databaseColumn = "lower($databaseColumn)";
            $value = strtolower($value);
        }

        if (in_array(SearchCasesEnum::WITHOUT_SPACES, $cases, true)) {
            $databaseColumn = strtolower($databaseColumn);
            $databaseColumn = "REGEXP_REPLACE($databaseColumn, r'\s', '')";
            $value = str_replace(' ', '', $value);
        }

        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("'", "\'", $value);

        $builder->where(DB::raw($databaseColumn), 'like', '%'.$value.'%');
    }
}
