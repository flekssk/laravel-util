<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions\Conditions;

use Exception;

class EqualsCondition extends Condition
{
    public const STRING_TYPE = 'string';
    public const DATE_TYPE = 'date';

    private ?string $data;

    public function __construct(string $filterParam, ?string $data, string $type)
    {
        if (!in_array($type, self::allowedTypes())) {
            throw new Exception("Equals condition do not supports $type type.");
        }

        $this->filterParam = $filterParam;
        $this->type = $type;
        $this->data = $data;
    }

    public static function allowedTypes(): array
    {
        return [
            self::STRING_TYPE,
            self::DATE_TYPE,
        ];
    }

    public function getData(): ?string
    {
        return $this->data;
    }
}