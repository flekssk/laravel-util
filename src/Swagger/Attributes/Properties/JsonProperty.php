<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class JsonProperty extends Property
{
    public function __construct(string $name, string $example = '{"test": "test"}', string $description = null, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            description: $description,
            type: 'object',
            example: $example,
            deprecated: $deprecated,
        );
    }
}
