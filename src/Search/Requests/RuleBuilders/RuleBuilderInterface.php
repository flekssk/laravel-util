<?php

namespace FKS\Search\Requests\RuleBuilders;

use FKS\Search\ValueObjects\Conditions\Condition;

interface RuleBuilderInterface
{
    public function getRules(string $filterParamName): array;

    /**
     * @param $data
     * @return Condition[]
     */
    public function getConditions($data): array;
}
