<?php

namespace FKS\Http\Requests\RuleBuilders;

use FKS\ValueObjects\SearchConditions\Conditions\Condition;

interface RuleBuilderInterface
{
    public function getRules(): array;

    /**
     * @param $data
     * @return Condition[]
     */
    public function getConditions($data): array;
}
