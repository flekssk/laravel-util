<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class UuidProperty extends Property
{
    public function __construct(string $name, string $description = null, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            description: $description,
            type: 'string',
            example: 'd46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00',
            deprecated: $deprecated,
        );
    }
}
