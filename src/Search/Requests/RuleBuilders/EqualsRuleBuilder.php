<?php

declare(strict_types=1);

namespace FKS\Search\Requests\RuleBuilders;

use Exception;
use FKS\Search\ValueObjects\Conditions\EqualsCondition;

class EqualsRuleBuilder extends RuleBuilder
{
    public function __construct(
        string $filterParam,
        bool $escapeDotInParam = true,
        protected ?string $type = null,
    ) {
        if (!in_array($type, EqualsCondition::allowedTypes())) {
            throw new Exception("Equals rule builder do not supports $type type.");
        }

        parent::__construct($filterParam, $escapeDotInParam);
    }

    public function getRules(string $filterParamName): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());

        $typeRule = match ($this->type) {
            EqualsCondition::DATE_TYPE => 'date:Y-m-d',
            EqualsCondition::STRING_TYPE => 'string',
        };

        return [
            "$filterParamName.$filterParam" => $typeRule . ($this->nullable ? '|nullable' : '') . ($this->required ? "|required" : ''),
        ];
    }

    public function getConditions($data): array
    {
        return [
            new EqualsCondition($this->filterParam, $data, $this->type),
        ];
    }
}