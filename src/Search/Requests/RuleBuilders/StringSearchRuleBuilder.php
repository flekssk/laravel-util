<?php

declare(strict_types=1);

namespace FKS\Search\Requests\RuleBuilders;

use FKS\Search\Enums\SearchCasesEnum;
use FKS\Search\ValueObjects\Conditions\Condition;
use FKS\Search\ValueObjects\Conditions\SearchCondition;

class StringSearchRuleBuilder extends RuleBuilder
{
    /**
     * @param SearchCasesEnum|SearchCasesEnum[] $case
     */
    public function __construct(
        string $filterParam = null,
        public readonly SearchCasesEnum|array $case = SearchCasesEnum::CAST_TO_LOWER,
        public readonly int $minChars = 3,
        public readonly int $maxChars = 100,
    ) {
        parent::__construct($filterParam);
    }

    public function getRules(string $filterParamName): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());
        return [
            "$filterParamName.$filterParam" => "nullable|min:$this->minChars|max:$this->maxChars"
                . ($this->required ? "|required" : ''),
        ];
    }

    /**
     * @param $data
     * @return array|Condition[]
     */
    public function getConditions($data): array
    {
        if ($data) {
            return [new SearchCondition($this->getFilterParam(), $data, $this->case)];
        }
        return [];
    }
}
