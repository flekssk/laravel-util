<?php

declare(strict_types=1);

namespace FKS\Services\Search;

use FKS\Enums\SearchComponent\SortParamSchemaEnum;
use FKS\ValueObjects\SearchConditions\PerPagePaginator;

class SearchComponentConfig
{
    public function __construct(
        public readonly string $paginator = PerPagePaginator::class,
        public readonly string $availableFieldsParamName = 'available_fields',
        public readonly string $filterParamName = 'filter',
        public readonly string $sortParamName = 'sort',
        public readonly SortParamSchemaEnum $sortParamSchema = SortParamSchemaEnum::OBJECT,
    ) {
    }
}
