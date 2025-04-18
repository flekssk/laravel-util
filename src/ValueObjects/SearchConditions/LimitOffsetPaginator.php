<?php

namespace FKS\ValueObjects\SearchConditions;

use FKS\Contracts\PaginatorInterface;
use FKS\Http\Requests\SearchRequest;

class LimitOffsetPaginator implements PaginatorInterface
{
    private int $limit;
    private int $offset;

    public static function getPaginatorValidatorRules(int $perPageMaxValue): array
    {
        return [
            'limit' => 'integer|min:1|max:' . $perPageMaxValue,
            'offset' => 'integer|min:0',
        ];
    }

    public function applyPagination($builder): void
    {
        $builder->limit($this->limit)
            ->offset($this->offset);
    }

    public function setupFromRequest(SearchRequest $searchRequest): self
    {
        $this->limit = $searchRequest->get('limit', $searchRequest::getDefaultPerPage());

        $this->offset = $searchRequest->get('offset', 0);

        return $this;
    }
}
