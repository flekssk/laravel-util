<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class BooleanProperty extends Property
{
    public function __construct(string $name, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            type: 'boolean',
            example: true,
            deprecated: $deprecated,
        );
    }
}
