<?php

namespace FKS\Search\Requests\RuleBuilders;

use FKS\Search\ValueObjects\Conditions\ContainsCondition;

class ContainsRuleBuilder extends RuleBuilder
{
    protected ?string $type = 'string';
    protected bool $onlyContains = false;

    public function getRules(string $filterParamName): array
    {
        $filterParam = $this->escapeDotInParam
            ? str_replace('.', '\.', $this->getFilterParam())
            : $this->getFilterParam();
        $rules = [
            "$filterParamName.$filterParam" => 'array' . ($this->required ? "|required" : ''),
            "$filterParamName.$filterParam.contains" => 'array' . ($this->required && !$this->onlyContains ? "|required_without:filter.$filterParam.notcontains" : ($this->required ? "|required" : '')),
            "$filterParamName.$filterParam.contains.*" => $this->getTypeValidationRule() . ($this->nullable ? '|nullable' : '') . ($this->required && $this->onlyContains ? "|required" : ''),
        ];

        if (!$this->onlyContains) {
            $rules["$filterParamName.$filterParam.notcontains"] = 'array' . ($this->required ? "|required_without:filter.$filterParam.contains" : '');
            $rules["$filterParamName.$filterParam.notcontains.*"] = $this->getTypeValidationRule() . ($this->nullable ? '|nullable' : '');
        } else {
            $rules["$filterParamName.$filterParam.notcontains"] = 'prohibited';
        }

        return $rules;
    }

    public function getConditions($data): array
    {
        $filterParam = $this->getFilterParam();
        $conditions = [];
        foreach ($this->getOperators() as ['operator' => $operator, 'contains' => $contains]) {
            $value = $data[$operator] ?? null;
            if ($value === null) {
                continue;
            }
            $conditions[] = new ContainsCondition($filterParam, $value, $contains, $this->type);
        }
        return $conditions;
    }

    public function onlyContains(): self
    {
        $this->onlyContains = true;

        return $this;
    }

    private function getTypeValidationRule(): string
    {
        return match($this->type) {
            ContainsCondition::TYPE_INTEGER, ContainsCondition::TYPE_INTEGER_IN_ARRAY => ContainsCondition::TYPE_INTEGER,
            ContainsCondition::TYPE_BYTES, ContainsCondition::TYPE_BYTES_IN_ARRAY, ContainsCondition::TYPE_HEX_STRING => ContainsCondition::TYPE_BYTES,
            ContainsCondition::TYPE_STRING => ContainsCondition::TYPE_STRING,
            ContainsCondition::TYPE_BOOLEAN => ContainsCondition::TYPE_BOOLEAN,
        };
    }

    private function getOperators(): array
    {
        $operators = [
            [
                'operator' => 'contains',
                'contains' => true,
            ],
        ];

        if (!$this->onlyContains) {
            $operators[] = [
                'operator' => 'notcontains',
                'contains' => false,
            ];
        }

        return $operators;
    }

    public function getSwaggerType(): ?string
    {
        return match($this->type) {
            ContainsCondition::TYPE_INTEGER, ContainsCondition::TYPE_INTEGER_IN_ARRAY => ContainsCondition::TYPE_INTEGER,
            ContainsCondition::TYPE_BYTES, ContainsCondition::TYPE_BYTES_IN_ARRAY => ContainsCondition::TYPE_BYTES,
            default => parent::getSwaggerType()
        };
    }
}
