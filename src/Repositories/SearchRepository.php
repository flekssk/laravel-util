<?php

declare(strict_types=1);

namespace FKS\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use FKS\Collections\EntitiesCollection;
use FKS\Contracts\FKSPaginatorInterface;
use FKS\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Http\Requests\SortingRuleBuilders\Enums\SortAsEnum;
use FKS\ValueObjects\SearchConditions\Conditions\Condition;
use FKS\ValueObjects\SearchConditions\Pagination;
use FKS\ValueObjects\SearchConditions\SearchConditions;

abstract class SearchRepository extends Repository
{
    public static function getMapFilterParamToColumn(): array
    {
        return [];
    }

    public static function getMapSortFieldToColumn(): array
    {
        return [];
    }

    public static function getMapFilterParamToQueryBuilderClosure(): array
    {
        return [];
    }

    public function search(
        SearchConditions $conditions,
        BuilderContract $preparedBuilder = null,
        bool $returnBuilder = false,
    ): BuilderContract|EntitiesCollection|bool|Collection|null {
        if (is_null($preparedBuilder)) {
            $preparedBuilder = $this->getNewModelQuery();
        }

        $this->applyAllConditions($preparedBuilder, $conditions);

        return $returnBuilder ? $preparedBuilder : $this->getByQuery($preparedBuilder);
    }

    protected function getJoinsDefinitions(): array
    {
        return [];
    }

    protected function getAdditionalConditionsDefinitions(): array
    {
        return [];
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applySearchConditions($builder, SearchConditions $searchConditions): void
    {
        foreach ($searchConditions->getFilter() as $condition) {
            $columnParts = explode('.', $condition->getFilterParam());
            $paramsKey = array_shift($columnParts);

            $columnName = static::getMapFilterParamToColumn()[$paramsKey] ?? $condition->getFilterParam();
            $queryBuilderClosure = static::getMapFilterParamToQueryBuilderClosure()[$condition->getFilterParam()] ?? null;
            if ($queryBuilderClosure) {
                $queryBuilderClosure($builder, $condition, $columnName);
                continue;
            }
            /** @var SearchQueryBuilderFactoryInterface $factory */
            $factory = App::make(SearchQueryBuilderFactoryInterface::class);
            $queryBuilder = $factory->getQueryBuilder(get_class($condition));
            if (is_null($queryBuilder)) {
                continue;
            }
            $queryBuilder->applyCondition($builder, $condition, $columnName);
        }
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applySorting($builder, SearchConditions $searchConditions): void
    {
        foreach ($searchConditions->getSort() as $sortItem) {
            $sortAs = SortAsEnum::tryFrom($sortItem['sort_as'] ?? '');
            $direction = $sortItem['direction'];
            $fieldParts = explode('.', $sortItem['field']);
            $sortingTableName = array_shift($fieldParts);
            $sortingTableColumnName = array_shift($fieldParts);
            $field = static::getMapSortFieldToColumn()[$sortingTableName]
                ?? static::getMapSortFieldToColumn()[$sortItem['field']]
                ?? $sortItem['field'];

            if ($field instanceof ColumnParamMap) {
                if ($field->tableValuesColumn !== null) {
                    $builder->where($field->tableName . '.' . $field->tableColumn, $sortingTableColumnName);
                }
                $field = $field->tableName . '.' . $field->tableValuesColumn;
            }

            if ($field instanceof ColumnParamRaw) {
                $field = DB::raw($field->raw);
            }

            if ($sortAs !== null) {
                $castAs = match ($sortAs) {
                    SortAsEnum::INTEGER => 'INT64',
                    SortAsEnum::FLOAT => 'FLOAT64',
                };
                $builder->orderByRaw("SAFE_CAST($field as $castAs) $direction");
            } else {
                $builder->orderBy($field, $direction);
            }
        }
    }

    /**
     * @param $builder
     * @param Pagination $paginate
     * @return void
     */
    protected function applyPagination($builder, FKSPaginatorInterface $paginate): void
    {
        $paginate->applyPagination($builder);
    }

    /**
     * @param $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applyAllConditions($builder, SearchConditions $searchConditions): void
    {
        $this->applyJoins($builder, $searchConditions);
        $this->applySearchConditions($builder, $searchConditions);
        $this->applySorting($builder, $searchConditions);
        $this->applyPagination($builder, $searchConditions->getPagination());
        $this->applyAdditionalSearchConditions($builder, $searchConditions);
    }

    /**
     * @param $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applyAllConditionsForCounts($builder, SearchConditions $searchConditions): void
    {
        $this->applyJoins($builder, $searchConditions);
        $this->applySearchConditions($builder, $searchConditions);
        $this->applyAdditionalSearchConditions($builder, $searchConditions);
    }

    /**
     * @param $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applyAdditionalSearchConditions($builder, SearchConditions $searchConditions): void
    {
        foreach ($this->getAdditionalConditionsDefinitions() as $condition) {
            if (is_callable($condition)) {
                $condition($builder, $searchConditions);
            } else {
                $this->$condition($builder, $searchConditions);
            }
        }
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applyJoins($builder, SearchConditions $searchConditions): void
    {
        $needApply = static function ($key) use ($searchConditions): bool {
            foreach ($searchConditions->getSort() as ['field' => $field]) {
                $columnParts = explode('.', $field);
                $joinParamsKey = array_shift($columnParts);
                $column = static::getMapSortFieldToColumn()[$joinParamsKey] ?? '';
                $column = $column instanceof ColumnParamMap ? $column->tableName . '.' . $column->tableColumn : $column;
                $column = ($column instanceof ColumnParamRaw && $column->tableName)
                    ? $column->tableName . '.'
                    : $column;

                if (str_starts_with($column, "$key.")) {
                    return true;
                }
            }
            /** @var Condition $condition */
            foreach ($searchConditions->getFilter() as $condition) {
                $columnParts = explode('.', $condition->getFilterParam());
                $joinParamsKey = array_shift($columnParts);
                $column = static::getMapFilterParamToColumn()[$joinParamsKey] ?? '';
                $column = $column instanceof ColumnParamMap ? $column->tableName . '.' . $column->tableColumn : $column;
                if (str_starts_with($column, "$key.")) {
                    return true;
                }
            }
            return false;
        };
        foreach ($this->getJoinsDefinitions() as $key => $join) {
            if (is_int($key) || $needApply($key)) {
                if (is_callable($join)) {
                    $join($builder, $searchConditions);
                } else {
                    $this->$join($builder, $searchConditions);
                }
            }
        }
    }

    /**
     * @param $collection
     * @param array $onlyFields
     * @return mixed
     */
    protected function only($collection, array $onlyFields)
    {
        $rows = $collection;
        foreach ($rows as $id => $rowData) {
            $rows[$id] = $rowData->only($onlyFields);
        }

        return $rows;
    }
}
