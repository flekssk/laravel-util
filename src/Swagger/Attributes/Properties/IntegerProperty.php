<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class IntegerProperty extends Property
{
    public function __construct(string $name, int $example = 1, string $description = null, ?bool $deprecated = null, array|string|null $enum = null)
    {
        if (is_a($enum, \BackedEnum::class, true)) {
            $enum = array_values(
                array_filter(
                    $enum::cases(),
                    fn (\BackedEnum $case) => $case->value !== null,
                )
            );
        }

        if ($example === null && $enum !== null) {
            $example = $enum[0];
        }

        parent::__construct(
            property: $name,
            description: $description,
            type: 'integer',
            enum: $enum,
            example: $example,
            deprecated: $deprecated
        );
    }
}
