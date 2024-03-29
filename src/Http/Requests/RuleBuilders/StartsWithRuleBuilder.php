<?php

namespace FKS\Http\Requests\RuleBuilders;


use FKS\ValueObjects\SearchConditions\Conditions\StartsWithCondition;

class StartsWithRuleBuilder extends RuleBuilder
{
    protected ?string $type = 'string';

    public function getRules(): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());
        return [
            "filter.$filterParam" => 'string|min:3' . ($this->required ? "|required" : '') . ($this->isNullable() ? '|nullable' : ''),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getConditions($data): array
    {
        return [
            new StartsWithCondition($this->getFilterParam(), $data),
        ];
    }
}
