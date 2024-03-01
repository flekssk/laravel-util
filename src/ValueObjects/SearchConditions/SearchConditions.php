<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions;

use Illuminate\Support\Collection;
use FKS\Collections\SearchConditionsCollection;
use FKS\Contracts\FKSPaginatorInterface;

final class SearchConditions
{
    private array $availableFields;
    private SearchConditionsCollection $filter;
    private Collection $additionalParams;
    private bool $onlyCounter;
    private Collection $sort;
    private FKSPaginatorInterface $pagination;

    public function __construct(
        array $availableFields,
        SearchConditionsCollection $filter,
        Collection $additionalParams,
        bool $onlyCounter,
        Collection $sort,
        FKSPaginatorInterface $pagination
    ) {
        $this->availableFields = $availableFields;
        $this->filter = $filter;
        $this->additionalParams = $additionalParams;
        $this->onlyCounter = $onlyCounter;
        $this->sort = $sort;
        $this->pagination = $pagination;
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

    public function getPagination(): FKSPaginatorInterface
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
}
