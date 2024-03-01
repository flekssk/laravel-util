<?php

declare(strict_types=1);

namespace FKS\Repositories;

use Elastic\Elasticsearch\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use FKS\Contracts\FKSPaginatorInterface;
use FKS\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Enums\SearchComponent\SearchDriversEnum;
use FKS\Facades\FKSLogger;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;
use FKS\ValueObjects\SearchConditions\SearchConditions;

abstract class ElasticSearchRepository
{
    protected readonly ElasticSearchQueryBuilder $builder;

    protected abstract function getIndexName(): string;

    public function __construct(
        protected Client $client,
    ) {
        $this->builder = new ElasticSearchQueryBuilder($this->getIndexName());
    }

    public function getQueryBuilder(): ElasticSearchQueryBuilder
    {
        return $this->builder;
    }

    public function get()
    {
        return $this->client->search($this->builder->getQuery());
    }

    protected function getMapFilterParamToColumn(): array
    {
        return [];
    }

    protected function getMapSortFieldToColumn(): array
    {
        return [];
    }

    protected function getMapFilterParamToQueryBuilderClosure(): array
    {
        return [];
    }

    protected function getAdditionalConditionsDefinitions(): array
    {
        return [];
    }

    protected function applySearchConditions(ElasticSearchQueryBuilder $builder, SearchConditions $searchConditions): void
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
            $factory->setSearchDriver(SearchDriversEnum::ELASTICSEARCH);
            $queryBuilder = $factory->getQueryBuilder(get_class($condition));
            if (is_null($queryBuilder)) {
                FKSLogger::warning(sprintf(
                    'Could not find query builder for %s in %s driver',
                    get_class($condition),
                    SearchDriversEnum::ELASTICSEARCH->name
                ));
                continue;
            }
            $queryBuilder->applyCondition($builder, $condition, $columnName);
        }
    }

    protected function applySorting(ElasticSearchQueryBuilder $builder, SearchConditions $searchConditions): void
    {
        foreach ($searchConditions->getSort() as $sortItem) {
            $field = static::getMapSortFieldToColumn()[$sortItem['field']]
                ?? $sortItem['field'];

            $builder->orderBy($field, $sortItem['direction']);
        }
    }

    protected function applyPagination($builder, FKSPaginatorInterface $paginate): void
    {
        $paginate->applyPagination($builder);
    }

    protected function applyAllConditions(ElasticSearchQueryBuilder $builder, SearchConditions $searchConditions): void
    {
        $this->applySearchConditions($builder, $searchConditions);
        $this->applySorting($builder, $searchConditions);
        $this->applyAdditionalSearchConditions($builder, $searchConditions);
        $this->applyPagination($builder, $searchConditions->getPagination());
    }

    protected function applyAllConditionsForCounts($builder, SearchConditions $searchConditions): void
    {
        $this->applySearchConditions($builder, $searchConditions);
        $this->applyAdditionalSearchConditions($builder, $searchConditions);
    }

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

    protected function only($collection, array $onlyFields)
    {
        $rows = $collection;
        foreach ($rows as $id => $rowData) {
            $rows[$id] = Arr::only($rowData, $onlyFields);
        }

        return $rows;
    }
}
