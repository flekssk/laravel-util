<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Property;

class DateProperty extends Property
{
    public function __construct(string $name, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            type: 'string',
            example: '2022-01-01',
            deprecated: $deprecated,
        );
    }
}
