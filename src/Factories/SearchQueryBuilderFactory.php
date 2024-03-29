<?php

declare(strict_types=1);

namespace FKS\Factories;

use Illuminate\Support\Facades\App;
use FKS\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Enums\SearchComponent\SearchDriversEnum;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Repositories\SearchQueryBuilders\Spanner\BooleanQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\ContainsQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\DateRangeQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\EqualsQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\MemberSearchQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\MetadataQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\NullQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\NumericQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\SearchQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\Spanner\StartsWithQueryBuilder;
use FKS\ValueObjects\SearchConditions\Conditions\BooleanCondition;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;
use FKS\ValueObjects\SearchConditions\Conditions\EqualsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\MemberSearchCondition;
use FKS\ValueObjects\SearchConditions\Conditions\MetadataCondition;
use FKS\ValueObjects\SearchConditions\Conditions\EmptyOrNullCondition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;
use FKS\ValueObjects\SearchConditions\Conditions\SearchCondition;
use FKS\ValueObjects\SearchConditions\Conditions\StartsWithCondition;

class SearchQueryBuilderFactory implements SearchQueryBuilderFactoryInterface
{
    private array $config;

    public function __construct(
        private SearchDriversEnum $driver = SearchDriversEnum::SPANNER
    ) {
        $this->config = [
            SearchDriversEnum::SPANNER->name => [
                BooleanCondition::class => BooleanQueryBuilder::class,
                ContainsCondition::class => ContainsQueryBuilder::class,
                DateRangeCondition::class => DateRangeQueryBuilder::class,
                MemberSearchCondition::class => MemberSearchQueryBuilder::class,
                NumericCondition::class => NumericQueryBuilder::class,
                SearchCondition::class => SearchQueryBuilder::class,
                StartsWithCondition::class => StartsWithQueryBuilder::class,
                MetadataCondition::class => MetadataQueryBuilder::class,
                EmptyOrNullCondition::class => NullQueryBuilder::class,
                EqualsCondition::class => EqualsQueryBuilder::class,
            ],
            SearchDriversEnum::ELASTICSEARCH->name => [
                BooleanCondition::class => \FKS\Repositories\SearchQueryBuilders\ElasticSearch\BooleanQueryBuilder::class,
                ContainsCondition::class => \FKS\Repositories\SearchQueryBuilders\ElasticSearch\ContainsQueryBuilder::class,
                DateRangeCondition::class => \FKS\Repositories\SearchQueryBuilders\ElasticSearch\DateRangeQueryBuilder::class,
                MemberSearchCondition::class => \FKS\Repositories\SearchQueryBuilders\ElasticSearch\MemberSearchQueryBuilder::class,
                NumericCondition::class => \FKS\Repositories\SearchQueryBuilders\ElasticSearch\NumericQueryBuilder::class,
                SearchCondition::class => \FKS\Repositories\SearchQueryBuilders\ElasticSearch\SearchQueryBuilder::class,
                StartsWithCondition::class => \FKS\Repositories\SearchQueryBuilders\ElasticSearch\StartsWithQueryBuilder::class,
                EmptyOrNullCondition::class => NullQueryBuilder::class,
            ],
        ];
    }

    public function set(string $conditionClass, string $queryBuilderClass, SearchDriversEnum $driver = SearchDriversEnum::SPANNER): void
    {
        $this->config[$driver->name][$conditionClass] = $queryBuilderClass;
    }

    public function getQueryBuilder(string $conditionClass): ?BuilderInterface
    {
        $builderClass = $this->config[$this->driver->name][$conditionClass] ?? null;
        if (is_null($builderClass)) {
            return null;
        }
        return App::make($builderClass);
    }

    public function setSearchDriver(SearchDriversEnum $driver): void
    {
        $this->driver = $driver;
    }
}
