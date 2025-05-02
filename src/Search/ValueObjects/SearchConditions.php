<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects;

use Illuminate\Support\Collection;
use FKS\Search\Collections\SearchConditionsCollection;
use FKS\Search\Contracts\PaginatorInterface;

final class SearchConditions
{
    public function __construct(
        private array $availableFields,
        private readonly SearchConditionsCollection $filter,
        private readonly Collection $additionalParams,
        private readonly bool $onlyCounter,
        private readonly Collection $sort,
        private readonly PaginatorInterface $pagination,
    ) {
    }

    public function getAvailableFields(): array
    {
        return $this->availableFields;
    }

    public function getFilter(): SearchConditionsCollection
    {
        return $this->filter;
    }

    public function getAdditionalParams(): Collection
    {
        return $this->additionalParams;
    }

    public function isOnlyCounter(): bool
    {
        return $this->onlyCounter;
    }

    public function getSort(): Collection
    {
        return $this->sort;
    }

    public function getPagination(): PaginatorInterface
    {
        return $this->pagination;
    }

    public function removeAvailableField(string $fieldName): void
    {
        $index = array_search($fieldName, $this->availableFields, true);

        if ($index !== false) {
            unset($this->availableFields[$index]);
        }
    }

    public function addAvailableField(string $field, string $index = null): void
    {
        if ($index === null) {
            $this->availableFields[] = $field;
        } else {
            $this->availableFields[$index] = $field;
        }
    }

    public function clearAvailableFields(): SearchConditions
    {
        $this->availableFields = [];

        return $this;
    }
}
