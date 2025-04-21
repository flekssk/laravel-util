<?php

namespace FKS\Http\Requests\RuleBuilders;

use FKS\ValueObjects\SearchConditions\Conditions\BooleanCondition;


class BooleanRuleBuilder extends RuleBuilder
{
    public function getRules(string $filterParamName): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());

        return [
            "$filterParamName.$filterParam" => 'boolean' . ($this->required ? "|required" : ''),
        ];
    }

    public function getConditions($data): array
    {
         return [new BooleanCondition($this->getFilterParam(), (bool) $data)];
    }
}
