<?php

declare(strict_types=1);

namespace FKS\Http\Requests\RuleBuilders;

use FKS\Enums\SearchComponent\SearchCasesEnum;
use FKS\ValueObjects\SearchConditions\Conditions\SearchCondition;

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

    public function getRules(): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());
        return [
            "filter.$filterParam" => "nullable|min:$this->minChars|max:$this->maxChars"
                . ($this->required ? "|required" : ''),
        ];
    }

    /**
     * @param $data
     * @return array|\FKS\ValueObjects\SearchConditions\Conditions\Condition[]
     */
    public function getConditions($data): array
    {
        if ($data) {
            return [new SearchCondition($this->getFilterParam(), $data, $this->case)];
        }
        return [];
    }
}
