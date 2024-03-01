<?php

declare(strict_types=1);

namespace FKS\Contracts;

use FKS\Enums\SearchComponent\SearchDriversEnum;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;

interface SearchQueryBuilderFactoryInterface
{
    public function set(string $conditionClass, string $queryBuilderClass);

    public function getQueryBuilder(string $conditionClass): ?BuilderInterface;

    public function setSearchDriver(SearchDriversEnum $driver);
}
