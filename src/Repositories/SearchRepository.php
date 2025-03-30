<?php

declare(strict_types=1);

namespace FKS\Repositories;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use FKS\Collections\EntitiesCollection;
use FKS\Contracts\SxopePaginatorInterface;
use FKS\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Factories\ColumnParamFactory;
use FKS\Repositories\DTO\CountsResultDTO;
use FKS\ValueObjects\SearchConditions\Conditions\Condition;
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
        bool $skipSorting = false
    ): BuilderContract|EntitiesCollection|bool|Collection|null {
        if (is_null($preparedBuilder)) {
            $preparedBuilder = $this->getNewModelQuery();
        }

        $this->applyAllConditions($preparedBuilder, $conditions, $skipSorting);

        return $returnBuilder ? $preparedBuilder : $this->getByQuery($preparedBuilder);
    }

    public function counts(
        SearchConditions $searchConditions,
        BuilderContract $preparedBuilder = null,
        bool $returnBuilder = false
    ): BuilderContract|CountsResultDTO {
        if (is_null($preparedBuilder)) {
            $preparedBuilder = $this->getQuery();
        }

        $selectFieldsToGroupByString = '';
        if (!empty($searchConditions->getAvailableFields())) {
            foreach ($searchConditions->getAvailableFields() as $availableField) {
                $mappedAvailableField = self::getMapFilterParamToColumn()[$availableField] ?? $availableField;
                $availableFieldForSelect = self::getMapFilterParamToColumn()[$availableField] ??  static::getEntityInstance()->getTable() . '.' . $availableField;
                $selectFieldsToGroupByString .= $availableFieldForSelect . ',';
                $preparedBuilder->groupBy($mappedAvailableField);
            }
        }

        $preparedBuilder->selectRaw($selectFieldsToGroupByString . 'COUNT(*) AS count');
        $this->applyAllConditionsForCounts($preparedBuilder, $searchConditions);

        if ($returnBuilder) {
            return $preparedBuilder;
        }

        $totalCounts = 0;
        $list = new Collection();
        $counts = $preparedBuilder->get();

        if (!empty($searchConditions->getAvailableFields())) {
            foreach ($counts as $item) {
                $onlyData = $item->only($searchConditions->getAvailableFields());
                $onlyData['count'] = $item['count'];
                $list->add($onlyData);
                $totalCounts += $item['count'];
            }
        } else {
            $totalCounts = $counts[0]['count'];
        }

        return new CountsResultDTO($totalCounts, $list->toArray());
    }

    public function getSortFieldDefinition(array $sortDefinition)
    {
        $tableName = $this->extractTableNameFrom($sortDefinition['field']);

        return static::getMapSortFieldToColumn()[$tableName]
            ?? static::getMapSortFieldToColumn()[$sortDefinition['field']]
            ?? $sortDefinition['field'];
    }

    protected function getJoinsDefinitions(): array
    {
        return [];
    }

    protected function getAdditionalConditionsDefinitions(): array
    {
        return [];
    }

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
     * @throws Exception
     */
    protected function applySorting($builder, SearchConditions $searchConditions): void
    {
        foreach ($searchConditions->getSort() as $sortDefinition) {
            $columnParam = ColumnParamFactory::create($this->getSortFieldDefinition($sortDefinition));
            $columnParam->applySortConditions($builder, $sortDefinition);
            $columnParam->applySort($builder, $sortDefinition);
        }
    }

    /**
     * @param $builder
     * @param SxopePaginatorInterface $paginate
     * @return void
     */
    protected function applyPagination($builder, SxopePaginatorInterface $paginate): void
    {
        $paginate->applyPagination($builder);
    }

    /**
     * @param $builder
     * @param SearchConditions $searchConditions
     * @return void
     * @throws Exception
     */
    protected function applyAllConditions($builder, SearchConditions $searchConditions, bool $skipSorting = false): void
    {
        $this->applyJoins($builder, $searchConditions);
        $this->applySearchConditions($builder, $searchConditions);
        if (!$skipSorting) {
            $this->applySorting($builder, $searchConditions);
        }
        if ($searchConditions->getPagination() !== null) {
            $this->applyPagination($builder, $searchConditions->getPagination());
        }
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
        $needApply = static function ($key) use ($searchConditions, $builder): bool {
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

            $columns = $builder?->getQuery()?->columns ?? [];
            foreach ($columns as $column) {
                if(is_string($column)) {
                    $columnParts = explode('.', $column);
                    if ($columnParts[0] === $key) {
                        return true;
                    }
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

    private function extractTableNameFrom(string $sortField): ?string
    {
        $fieldParts = explode('.', $sortField);

        return array_shift($fieldParts);
    }
}
