<?php

declare(strict_types=1);

namespace FKS\Factories;

use FKS\ValueObjects\SearchConditions\Conditions\StringCondition;
use Illuminate\Support\Facades\App;
use FKS\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Enums\SearchComponent\SearchDriversEnum;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Repositories\SearchQueryBuilders\MySQL\BooleanQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\ContainsQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\DateRangeQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\EqualsQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\MetadataQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\NullQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\NumericQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\SearchQueryBuilder;
use FKS\Repositories\SearchQueryBuilders\MySQL\StartsWithQueryBuilder;
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
        private SearchDriversEnum $driver = SearchDriversEnum::MYSQL
    ) {
        $this->config = [
            SearchDriversEnum::MYSQL->name => [
                BooleanCondition::class => BooleanQueryBuilder::class,
                ContainsCondition::class => ContainsQueryBuilder::class,
                DateRangeCondition::class => DateRangeQueryBuilder::class,
                NumericCondition::class => NumericQueryBuilder::class,
                SearchCondition::class => SearchQueryBuilder::class,
                StartsWithCondition::class => StartsWithQueryBuilder::class,
                MetadataCondition::class => MetadataQueryBuilder::class,
                EmptyOrNullCondition::class => NullQueryBuilder::class,
                EqualsCondition::class => EqualsQueryBuilder::class,
                StringCondition::class => EqualsQueryBuilder::class,
            ],
        ];
    }

    public function set(string $conditionClass, string $queryBuilderClass, SearchDriversEnum $driver = SearchDriversEnum::MYSQL): void
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
