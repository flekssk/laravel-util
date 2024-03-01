<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions\Conditions;

abstract class Condition
{
    protected ?string $filterParam = null;
    protected ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getFilterParam(): ?string
    {
        return $this->filterParam;
    }
}
