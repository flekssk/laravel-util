<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

use DomainException;

class StringCondition extends Condition
{
    public const TYPE_STRING = 'string';

    protected string $value;
    protected string $operator;

    public static function allowedTypes(): array
    {
        return [self::TYPE_STRING];
    }

    public function __construct(string $filterParam, string $value, string $operator, string $type)
    {
        if (!in_array($type, self::allowedTypes(), true)) {
            throw new DomainException("Bad type '$type'. Allowed only: " . implode(', ', self::allowedTypes()));
        }
        $this->value = $value;
        $this->operator = $operator;
        $this->filterParam = $filterParam;
        $this->type = $type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getData(): string
    {
        return $this->value;
    }
}
