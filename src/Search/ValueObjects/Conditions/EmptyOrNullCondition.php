<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

class EmptyOrNullCondition extends Condition
{
    public function __construct(string $filterParam)
    {
        $this->filterParam = $filterParam;
    }
}
