<?php

declare(strict_types=1);

namespace FKS\Search\Collections;

use Illuminate\Support\Collection;
use FKS\Search\ValueObjects\Conditions\Condition;

class SearchConditionsCollection extends Collection
{
    public function hasFilter(string $paramName): bool
    {
        return (bool) $this->first(
            static fn (Condition $filter) => $filter->getFilterParam() === $paramName,
            false
        );
    }

    public function hasAny(mixed $key): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->has($value)) {
                return true;
            }
        }

        return false;
    }

    public function has(mixed $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        $filterKeys = $this->map(fn (Condition $item) => $item->getFilterParam())
            ->toArray();

        foreach ($keys as $key) {
            if (! in_array($key, $filterKeys)) {
                return false;
            }
        }

        return true;
    }

    public function getFilter(string $paramName): Condition|Collection|null
    {
        $condition = $this->where(
            static fn (Condition $filter) => $filter->getFilterParam() === $paramName
        );
        return $condition->count() <= 1 ? $condition->first() : $condition;
    }

    public function removeFilter(string $paramName): void
    {
        $id = $this->search(static fn (Condition $condition) => $condition->getFilterParam() === $paramName);

        if ($id !== false) {
            $this->offsetUnset($id);
        }
    }

    /**
     * @deprecated
     * @param string $paramName
     * @return Condition|null
     */
    public function getAndRemoveFilter(string $paramName): ?Condition
    {
        $element = $this->getFilter($paramName);
        $this->removeFilter($paramName);

        return $element;
    }

    /**
     * @param string $paramName
     * @return SearchConditionsCollection<Condition>
     */
    public function getFilterCollectionByParamName(string $paramName): SearchConditionsCollection
    {
        return $this->where(
            static fn (Condition $filter) => $filter->getFilterParam() === $paramName
        );
    }

    /**
     * @param string $paramName
     * @return void
     */
    public function removeFiltersByParamName(string $paramName): void
    {
        $filters = $this->getFilterCollectionByParamName($paramName);
        $this->forget($filters->keys()->toArray());
    }

    /**
     * @param string $paramName
     * @return SearchConditionsCollection<Condition>
     */
    public function getFilterCollectionAndRemoveByParamName(string $paramName): SearchConditionsCollection
    {
        $conditionsCollection = $this->getFilterCollectionByParamName($paramName);
        $this->removeFiltersByParamName($paramName);

        return $conditionsCollection;
    }
}
