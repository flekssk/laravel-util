<?php

namespace FKS\Search\Requests\RuleBuilders;

use FKS\Search\ValueObjects\Conditions\DateRangeCondition;

class TimestampRangeRuleBuilder extends DateRangeRuleBuilder
{
    public const FORMAT = 'Y-m-d H:i:s';
    protected ?string $type = DateRangeCondition::TYPE_TIMESTAMP;
}
