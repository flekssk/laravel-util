<?php

declare(strict_types=1);

namespace FKS\Contracts;

use FKS\Http\Requests\SearchRequest;

interface FKSPaginatorInterface
{
    public static function getPaginatorValidatorRules(int $perPageMaxValue): array;

    public function applyPagination($builder): void;

    public function setupFromRequest(SearchRequest $searchRequest): self;
}
