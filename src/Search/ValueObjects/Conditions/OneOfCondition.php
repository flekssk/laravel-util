<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

class OneOfCondition extends Condition
{
    /**
     * @param Condition[] $column
     */
    public function __construct(public readonly array $column)
    {
        $this->filterParam = '';
    }
}
