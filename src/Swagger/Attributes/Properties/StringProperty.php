<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class StringProperty extends Property
{
    public function __construct(string $name, string $example = null, array|\BackedEnum|string $enum = null, string $description = null, ?bool $deprecated = null)
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
            type: 'string',
            enum: $enum,
            example: $example ?? $name,
            deprecated: $deprecated,
        );
    }
}
