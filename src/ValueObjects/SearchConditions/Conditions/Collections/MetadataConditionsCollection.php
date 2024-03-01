<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions\Conditions\Collections;

use FKS\ValueObjects\SearchConditions\Conditions\Condition;

class MetadataConditionsCollection
{
    private array $conditions = [];

    /**
     * @param Condition[] $condition
     */
    public function add(string $metadataKey, array $condition): void
    {
        if (!isset($this->conditions[$metadataKey])) {
            $this->conditions[$metadataKey] = [];
        }

        $this->conditions[$metadataKey] = array_merge($this->conditions[$metadataKey], $condition);
    }

    public function all(): array
    {
        return $this->conditions;
    }
}