<?php

namespace FKS\Http\Requests\RuleBuilders;

use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;

class TimestampRangeRuleBuilder extends DateRangeRuleBuilder
{
    public const FORMAT = 'Y-m-d H:i:s';
    protected ?string $type = DateRangeCondition::TYPE_TIMESTAMP;
}
