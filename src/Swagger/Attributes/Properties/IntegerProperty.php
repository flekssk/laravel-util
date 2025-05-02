<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class IntegerProperty extends Property
{
    public function __construct(string $name, int $example = 1, string $description = null, ?bool $deprecated = null, array|string|null $enum = null)
    {
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
