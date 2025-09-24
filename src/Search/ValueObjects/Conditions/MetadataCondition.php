<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

use FKS\Search\ValueObjects\Conditions\Collections\MetadataConditionsCollection;

class MetadataCondition extends Condition
{
    public function __construct(
        public readonly MetadataConditionsCollection $subConditions,
        string $filterParam,
    ) {
        $this->filterParam = $filterParam;
    }
}
