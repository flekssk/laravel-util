<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Properties;

use OpenApi\Attributes\Property;

class StringProperty extends Property
{
    public function __construct(string $name, string $example = null, array $enum = null, string $description = null, ?bool $deprecated = null)
    {
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
