<?php

declare(strict_types=1);

namespace FKS\Search\Repositories;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use FKS\Repositories\Repository;
use FKS\Search\Collections\EntitiesCollection;
use FKS\Search\Contracts\PaginatorInterface;
use FKS\Search\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Search\DTO\CountsResultDTO;
use FKS\Search\Factories\ColumnParamFactory;
use FKS\Search\ValueObjects\Conditions\Condition;
use FKS\Search\ValueObjects\FiltersPreset;
use FKS\Search\ValueObjects\SearchConditions;

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

    public static function getMapAvailableFieldToSelect(): array
    {
        return [];
    }

    public static function getMapAvailableFieldToWith(): array
    {
        return [];
    }

    public static function getMapFilterParamToQueryBuilderClosure(): array
    {
        return [];
    }

    public static function excludeAvailableFieldsFromSelect(): array
    {
        return [];
    }

    /**
     * @return FiltersPreset[]
     */
    public static function getFiltersPresets(): array
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
                $availableFieldForSelect = self::getMapFilterParamToColumn()[$availableField] ??  static::getEntityInstance(
                )->getTable() . 'Repositories' . $availableField;
                $mapper = static::getMapAvailableFieldToSelect()[$availableField] ?? null;
                if ($mapper instanceof \Closure) {
                    $mapper($preparedBuilder);
                } else {
                    $selectFieldsToGroupByString .= $availableFieldForSelect . ',';
                }
                $preparedBuilder->groupBy($mappedAvailableField);
            }
        }

        $this->applyAllConditionsForCounts($preparedBuilder, $searchConditions);

        $preparedBuilder->selectRaw($selectFieldsToGroupByString . 'COUNT(*) AS count');

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

        return new CountsResultDTO($totalCounts, $this->getQuery()->count(), $list->toArray());
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

    protected function applyPagination($builder, PaginatorInterface $paginate): void
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
        $this->applyFiltersPresets($builder, $searchConditions);
        $this->applyJoins($builder, $searchConditions);
        $this->applySearchConditions($builder, $searchConditions);
        if (!$skipSorting) {
            $this->applySorting($builder, $searchConditions);
        }
        if ($searchConditions->getPagination() !== null) {
            $this->applyPagination($builder, $searchConditions->getPagination());
        }

        $select = [];

        if ($searchConditions->getAvailableFields() !== []) {
            foreach ($searchConditions->getAvailableFields() as $availableField) {
                if (in_array($availableField, static::excludeAvailableFieldsFromSelect(), true)) {
                    continue;
                }
                if (array_key_exists($availableField, static::excludeAvailableFieldsFromSelect())) {
                    $select = array_merge($select, static::excludeAvailableFieldsFromSelect()[$availableField]);
                    continue;
                }

                $mapper = static::getMapAvailableFieldToSelect()[$availableField] ?? null;
                if ($mapper instanceof \Closure) {
                    $mapper($builder);
                } elseif (is_a($mapper, Expression::class)) {
                    $select[] = $mapper;
                } elseif (is_array($mapper)) {
                    $select = array_merge($select, $mapper);
                } elseif (is_string($mapper)) {
                    $select[] = $mapper . ' AS ' . $availableField;
                } elseif(array_keys(static::getMapAvailableFieldToWith(), $availableField)) {
                    $builder->with(static::getMapAvailableFieldToWith()[$availableField]);
                } else {
                    $select[] = $this->entityInstance->getTable() . '.' . $availableField;
                }
            }

            $builder->select($select);
        }
    }

    /**
     * @param $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applyAllConditionsForCounts($builder, SearchConditions $searchConditions): void
    {
        $this->applyFiltersPresets($builder, $searchConditions);
        $this->applyJoins($builder, $searchConditions);
        $this->applySearchConditions($builder, $searchConditions);
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $builder
     * @param SearchConditions $searchConditions
     * @return void
     */
    protected function applyJoins($builder, SearchConditions $conditions): void
    {
        $joinsDefinitions = $this->getJoinsDefinitions();
        if (empty($joinsDefinitions)) {
            return;
        }

        $usedKeys = [];
        foreach (['availableFields', 'sort', 'filter'] as $type) {
            foreach ($conditions->{'get' . ucfirst($type)}() as $value) {
                $field = is_array($value) ? $value['field'] ?? '' : $value;
                if ($field instanceof Condition) {
                    $field = $field->getFilterParam();
                }
                $field = static::getMapFilterParamToColumn()[$field] ?? $field;
                $parts = explode('.', $field);
                $usedKeys[] = array_shift($parts);
            }
        }
        $usedKeys = array_unique($usedKeys);

        foreach ($joinsDefinitions as $key => $join) {
            if (is_int($key) || in_array($key, $usedKeys, true)) {
                if (is_callable($join)) {
                    $join($builder, $conditions);
                } else {
                    $this->$join($builder, $conditions);
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

    private function applyFiltersPresets($preparedBuilder, SearchConditions $conditions): void
    {
        $presets = static::getFiltersPresets();

        foreach ($presets as $preset) {
            $preset->apply($conditions, $preparedBuilder);
        }
    }
}
