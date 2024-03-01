<?php

namespace FKS\ValueObjects\SearchConditions\Conditions;

class StartsWithCondition extends Condition
{
    protected ?string $value;

    public function __construct(string $filterParam, ?string $value)
    {
        $this->filterParam = $filterParam;
        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
