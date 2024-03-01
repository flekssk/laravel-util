<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Properties;

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
            type: 'array',
            items: new Items(
                type: $arrayItemType,
                example: $example
            ),
            deprecated: $deprecated,
            required: $required,
        );
    }
}
