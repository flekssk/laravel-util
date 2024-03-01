<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions\Conditions;

use FKS\ValueObjects\SearchConditions\Conditions\Collections\MetadataConditionsCollection;

class MetadataCondition extends Condition
{
    public function __construct(
        public readonly MetadataConditionsCollection $subConditions,
        string $filterParam,
    ) {
        $this->filterParam = $filterParam;
    }
}
