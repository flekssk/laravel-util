<?php

namespace FKS\Search\Requests\RuleBuilders;

use FKS\Search\ValueObjects\Conditions\BooleanCondition;


class BooleanRuleBuilder extends RuleBuilder
{
    public function getRules(string $filterParamName): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());

        return [
            "$filterParamName.$filterParam" => 'boolean' . ($this->required ? "|required" : '') . ($this->isNullable() ? '|nullable' : ''),
        ];
    }

    public function getConditions($data): array
    {
         return [new BooleanCondition($this->getFilterParam(), (bool) $data)];
    }
}
