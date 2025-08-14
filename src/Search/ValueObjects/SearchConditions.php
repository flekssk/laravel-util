<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects;

use Illuminate\Support\Collection;
use FKS\Search\Collections\SearchConditionsCollection;
use FKS\Search\Contracts\PaginatorInterface;
use FKS\Search\Requests\SettingsDefinitions;
use FKS\Search\ValueObjects\Conditions\Condition;
use FKS\Serializer\SerializableObject;

final class SearchConditions extends SerializableObject
{
    public function __construct(
        private array $availableFields,
        private SearchConditionsCollection $filter,
        private Collection $sort,
        private ?PaginatorInterface $pagination,
        private readonly SettingsDefinitions $settingsDefinitions,
    ) {
        sort($this->availableFields);
        $this->sort = $this->sort->sortBy(fn ($item) => $item['field']);
        $this->filter = $this->filter->sortBy(fn (Condition $item) => $item->getFilterParam());
    }

    public function getAvailableFields(): array
    {
        return $this->availableFields;
    }

    public function pushFilter(Condition $condition): self
    {
        $this->filter->push($condition);

        return $this;
    }

    public function getFilter(): SearchConditionsCollection
    {
        return $this->filter;
    }

    /**
     * @template T of Condition
     *
     * @param class-string<T> $expectedCondition
     * @return Condition|T|null
     */
    public function pullFilter(string $filter, string $expectedCondition = null): ?Condition
    {
        $filter = $this->filter->getAndRemoveFilter($filter);

        if (
            $filter !== null
            && $expectedCondition !== null
            && !is_a($filter, $expectedCondition, true)
        ) {
            throw new \Exception("Invalid filter: $filter, expected: $expectedCondition");
        }

        return $filter;
    }

    /**
     * @template T of Condition
     *
     * @param class-string<T> $expectedCondition
     * @return T|null
     */
    public function findFilter(string $filter, string $expectedCondition = null): ?Condition
    {
        $filter = $this->filter->getFilter($filter);

        if (
            $filter !== null
            && $expectedCondition !== null
            && !is_a($filter, $expectedCondition, true)
        ) {
            throw new \Exception("Invalid filter: $filter, expected: $expectedCondition");
        }

        return $filter;
    }

    public function getSort(): Collection
    {
        return $this->sort;
    }

    public function getPagination(): ?PaginatorInterface
    {
        return $this->pagination;
    }

    public function setPagination(PaginatorInterface $paginator): static
    {
        $this->pagination = $paginator;

        return $this;
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

    public function hash(): string
    {
        return md5(serialize($this));
    }

    public function withoutPagination(): SearchConditions
    {
        return SearchConditions::make(
            $this->availableFields,
            $this->filter->toArray(),
            $this->sort->toArray(),
            null,
            $this->settingsDefinitions,
        );
    }

    public function withoutFilters(array $withoutFilters = []): SearchConditions
    {
        return SearchConditions::make(
            $this->availableFields,
            $withoutFilters === []
                ? []
                : $this->filter->filter(
                    static fn (Condition $condition) => !in_array($condition->getFilterParam(), $withoutFilters, true)
                )->all(),
            $this->sort->toArray(),
            $this->pagination,
            $this->settingsDefinitions,
        );
    }

    public function getSettingsDefinitions(): SettingsDefinitions
    {
        return $this->settingsDefinitions;
    }

    public static function make(
        array $availableFields,
        array $filter,
        array $sort = [],
        PaginatorInterface $paginator = null,
        SettingsDefinitions $settingsDefinitions = null,
    ): SearchConditions {
        return new self(
            $availableFields,
            SearchConditionsCollection::make($filter),
            Collection::make($sort),
            $paginator,
            $settingsDefinitions ?? new SettingsDefinitions(),
        );
    }
}
