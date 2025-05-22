<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class DateTimeFormattedProperty extends Property
{
    public function __construct(string $name, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            type: 'string',
            example: '04:06 PM 04/29/2025',
            deprecated: $deprecated,
        );
    }
}
