<?php

declare(strict_types=1);

namespace FKS\Search\Services;

use FKS\Search\ValueObjects\Conditions\Condition;
use FKS\Search\ValueObjects\Conditions\ContainsCondition;

class ConditionTransformerService
{
    public function transform(Condition $condition): array
    {
        return match ($condition::class) {
            ContainsCondition::class => [
                $condition->getFilterParam() => [
                    'contains' => $condition->getValues()
                ]
            ],
            default => throw new \Exception('Not implemented')
        };
    }
}
