<?php

namespace FKS\Http\Requests\RuleBuilders;

use FKS\ValueObjects\SearchConditions\Conditions\Condition;

interface RuleBuilderInterface
{
    public function getRules(string $filterParamName): array;

    /**
     * @param $data
     * @return Condition[]
     */
    public function getConditions($data): array;
}
