<?php

namespace FKS\Search\ValueObjects;

use FKS\Search\Contracts\PaginatorInterface;
use FKS\Search\Requests\SearchRequest;
use FKS\Swagger\Attributes\Properties\IntegerProperty;

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

    public function getSwaggerProperties(): array
    {
        return [
            new IntegerProperty('limit', description: 'Limit of the results'),
            new IntegerProperty('offset', description:  'Offset of the results'),
        ];
    }

    public function setOffset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public static function make(int $limit, int $offset): static
    {
        $paginator = new static();

        $paginator->limit = $limit;
        $paginator->offset = $offset;

        return $paginator;
    }
}
