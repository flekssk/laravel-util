<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Properties;

use OpenApi\Attributes\Property;

class FloatProperty extends Property
{
    public function __construct(string $name, float $example = 1.0, string $description = null, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            example: $example,
            description: $description,
            deprecated: $deprecated,
        );
    }
}
