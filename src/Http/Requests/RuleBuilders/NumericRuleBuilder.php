<?php

namespace FKS\Http\Requests\RuleBuilders;

use FKS\ValueObjects\SearchConditions\Conditions\Condition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;

class NumericRuleBuilder extends RuleBuilder
{
    protected ?string $type = 'numeric';
    protected bool $nullable = true;

    public function getRules(): array
    {
        $validationType = match($this->type) {
            NumericCondition::TYPE_NUMERIC => 'numeric',
            NumericCondition::TYPE_INTEGER, NumericCondition::TYPE_INTEGER_STRING => 'integer',
        };
        $filterParam = $this->escapeDotInParam
            ? str_replace('.', '\.', $this->getFilterParam())
            : $this->getFilterParam();
        return [
            "filter.$filterParam" => 'array' . ($this->required ? '|required' : ''),
            "filter.$filterParam.eq" => $validationType . ($this->nullable ? '|nullable' : ''),
            "filter.$filterParam.ne" => $validationType . ($this->nullable ? '|nullable' : ''),
            "filter.$filterParam.le" => $validationType . ($this->nullable ? '|nullable' : ''),
            "filter.$filterParam.lt" => $validationType . ($this->nullable ? '|nullable' : ''),
            "filter.$filterParam.gt" => $validationType . ($this->nullable ? '|nullable' : ''),
            "filter.$filterParam.ge" => $validationType . ($this->nullable ? '|nullable' : ''),
        ];
    }

    /**
     * @param $data
     * @return Condition[]
     */
    public function getConditions($data): array
    {
        $allowedOperators = array_keys(NumericCondition::getMapAllowedOperators());
        $conditions = [];
        foreach ($allowedOperators as $operator) {
            $value = $data[$operator] ?? null;
            if ($value === null) {
                continue;
            }
            $conditions[] = new NumericCondition($this->getFilterParam(), $value, $operator, $this->type);
        }
        return $conditions;
    }
}
