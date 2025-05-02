<?php

namespace FKS\Search\ValueObjects;

use FKS\Search\Contracts\PaginatorInterface;
use FKS\Search\Requests\SearchRequest;

class PerPagePaginator implements PaginatorInterface
{
    private int $page;
    private int $perPage;

    public static function getPaginatorValidatorRules(int $perPageMaxValue): array
    {
        return [
            'per_page' => 'integer|min:1|max:' . $perPageMaxValue,
            'page' => 'integer|min:1',
        ];
    }

    public function applyPagination($builder): void
    {
        $offset = $this->page === 1 ? 0 : ($this->page - 1) * $this->perPage;
        $builder->limit($this->perPage)
            ->offset($offset);
    }

    public function setupFromRequest(SearchRequest $searchRequest): self
    {
        $this->setPage($searchRequest->get('page', 1));
        $this->setPerPage($searchRequest->get('per_page', $searchRequest::getDefaultPerPage()));

        return $this;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }
}
