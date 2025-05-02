<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

class BooleanCondition extends Condition
{

    public const TYPE_BOOLEAN = 'boolean';
    protected bool $value;

    public static function allowedTypes(): array
    {
        return [self::TYPE_BOOLEAN];
    }

    public function __construct(string $filterParam, bool $value)
    {
        $this->filterParam = $filterParam;
        $this->value = $value;
        $this->type = self::TYPE_BOOLEAN;
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}
