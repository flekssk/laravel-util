<?php

declare(strict_types=1);

namespace FKS\Search\Contracts;

use FKS\Search\Requests\SearchRequest;

interface PaginatorInterface
{
    public static function getPaginatorValidatorRules(int $perPageMaxValue): array;

    public function applyPagination($builder): void;

    public function setupFromRequest(SearchRequest $searchRequest): self;
}
