<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions\Conditions;

use DomainException;

class NumericCondition extends Condition
{
    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_INTEGER_STRING = 'integer_string';

    public static function allowedTypes(): array
    {
        return [self::TYPE_NUMERIC, self::TYPE_INTEGER, self::TYPE_INTEGER_STRING];
    }

    public function __construct(
        protected ?string $filterParam,
        public readonly float $value,
        public readonly string $operator,
        protected ?string $type
    ) {
        if (!in_array($type, self::allowedTypes(), true)) {
            throw new DomainException("Bad type '$type'. Allowed only: " . implode(', ', self::allowedTypes()));
        }
        $allowedOperators = array_keys(self::getMapAllowedOperators());
        if (!in_array($operator, $allowedOperators, true)) {
            throw new DomainException("Bad operator '$operator'. Allowed only: " . implode(', ', $allowedOperators));
        }
    }

    public static function getMapAllowedOperators(): array
    {
        return [
            'eq' => '=',
            'ne' => '!=',
            'le' => '<=',
            'ge' => '>=',
            'lt' => '<',
            'gt' => '>',
        ];
    }

    public function getValue(): float|int|string
    {
        return $this->isInteger() ? (int) $this->value : $this->value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getMathematicalOperator(): string
    {
        return static::getMapAllowedOperators()[$this->operator];
    }

    public function isNumeric(): bool
    {
        return $this->type === self::TYPE_NUMERIC;
    }

    public function isInteger(): bool
    {
        return $this->type === self::TYPE_INTEGER;
    }

    public function isString(): bool
    {
        return $this->type === self::TYPE_INTEGER_STRING;
    }
}
