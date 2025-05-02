<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Properties;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;

class ArrayProperty extends Property
{
    public function __construct(
        string $propertyName,
        string $arrayItemType = 'string',
        string $example = null,
        ?bool $deprecated = null,
        ?array $required = null,
    )
    {
        parent::__construct(
            property: $propertyName,
            required: $required,
            type: 'array',
            items: new Items(
                type: $arrayItemType,
                example: $example
            ),
            deprecated: $deprecated,
        );
    }
}
