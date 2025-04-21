<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions\Conditions;

use RuntimeException;

class ContainsCondition extends Condition
{
    public const TYPE_BYTES = 'uuid_or_hex';
    public const TYPE_HEX_STRING = 'hex_string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER_IN_ARRAY = 'integer_in_array';
    public const TYPE_BYTES_IN_ARRAY = 'bytes_in_array';
    public const TYPE_BOOLEAN = 'boolean';

    private array $values;
    private bool $contains;

    public static function allowedTypes(): array
    {
        return [
            self::TYPE_INTEGER,
            self::TYPE_BYTES,
            self::TYPE_STRING,
            self::TYPE_INTEGER_IN_ARRAY,
            self::TYPE_BYTES_IN_ARRAY,
            self::TYPE_BOOLEAN,
            self::TYPE_HEX_STRING,
        ];
    }

    public function __construct(string $filterParam, array $values, bool $contains, string $type)
    {
        if (!in_array($type, self::allowedTypes(), true)) {
            throw new RuntimeException("Bad type '$type'. Allowed only: " . implode(', ', self::allowedTypes()));
        }
        $this->filterParam = $filterParam;
        $this->values = $values;
        $this->contains = $contains;
        $this->type = $type;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function isContains(): bool
    {
        return $this->contains;
    }

    public function isBytes(): bool
    {
        return in_array(
            $this->type,
            [ContainsCondition::TYPE_BYTES, ContainsCondition::TYPE_BYTES_IN_ARRAY]
        );
    }

    public function isString(): bool
    {
        return $this->type === self::TYPE_STRING;
    }

    public function isInteger(): bool
    {
        return $this->type === self::TYPE_INTEGER;
    }

    public function isBoolean(): bool
    {
        return $this->type === self::TYPE_BOOLEAN;
    }

    public function isInArray(): bool
    {
        return in_array(
            $this->type,
            [ContainsCondition::TYPE_INTEGER_IN_ARRAY, ContainsCondition::TYPE_BYTES_IN_ARRAY]
        );
    }

    public function isHexString(): bool
    {
        return $this->type === ContainsCondition::TYPE_HEX_STRING;
    }
}
