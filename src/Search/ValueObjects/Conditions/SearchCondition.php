<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

use FKS\Search\Enums\SearchCasesEnum;

class SearchCondition extends Condition
{
    /**
     * @param SearchCasesEnum|SearchCasesEnum[] $case
     */
    public function __construct(
        string $filterParam,
        public readonly string $value,
        public readonly SearchCasesEnum|array $case = SearchCasesEnum::CAST_TO_LOWER
    ) {
        $this->filterParam = $filterParam;
    }
}
