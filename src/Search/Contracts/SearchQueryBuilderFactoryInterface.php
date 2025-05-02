<?php

declare(strict_types=1);

namespace FKS\Search\Contracts;

use FKS\Search\Enums\SearchDriversEnum;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;

interface SearchQueryBuilderFactoryInterface
{
    public function set(string $conditionClass, string $queryBuilderClass);

    public function getQueryBuilder(string $conditionClass): ?BuilderInterface;

    public function setSearchDriver(SearchDriversEnum $driver);
}
