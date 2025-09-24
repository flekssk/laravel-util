<?php

declare(strict_types=1);

namespace FKS\Search\Factories;

use Illuminate\Support\Facades\App;
use FKS\Search\Contracts\SearchQueryBuilderFactoryInterface;
use FKS\Search\Enums\SearchDriversEnum;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\BooleanQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\ContainsQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\DateRangeQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\EqualsQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\MetadataQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\NullQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\NumericQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\OneOfQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\SearchQueryBuilder;
use FKS\Search\Repositories\SearchQueryBuilders\MySQL\StartsWithQueryBuilder;
use FKS\Search\ValueObjects\Conditions\BooleanCondition;
use FKS\Search\ValueObjects\Conditions\ContainsCondition;
use FKS\Search\ValueObjects\Conditions\DateRangeCondition;
use FKS\Search\ValueObjects\Conditions\EmptyOrNullCondition;
use FKS\Search\ValueObjects\Conditions\EqualsCondition;
use FKS\Search\ValueObjects\Conditions\MetadataCondition;
use FKS\Search\ValueObjects\Conditions\NumericCondition;
use FKS\Search\ValueObjects\Conditions\OneOfCondition;
use FKS\Search\ValueObjects\Conditions\SearchCondition;
use FKS\Search\ValueObjects\Conditions\StartsWithCondition;
use FKS\Search\ValueObjects\Conditions\StringCondition;

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
                OneOfCondition::class => OneOfQueryBuilder::class,
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
