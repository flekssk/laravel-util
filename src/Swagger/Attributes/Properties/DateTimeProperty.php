<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class DateTimeProperty extends Property
{   
    public function __construct(string $name, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            type: 'string',
            example: '2025-04-29 16:06:29',
            deprecated: $deprecated,
        );
    }
}
