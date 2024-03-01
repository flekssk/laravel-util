<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Properties;

use OpenApi\Attributes\Property;

class FileProperty extends Property
{
    public function __construct(string $name, string $example = null, string $description = null, ?bool $deprecated = null)
    {
        parent::__construct(
            property: $name,
            description: $description,
            type: 'file',
            example: $example,
            deprecated: $deprecated,
        );
    }
}
