<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Parameters;

use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

class UuidParameter extends Parameter
{
    public function __construct(string $name, string $description = null, string $in = 'path', bool $required = true)
    {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $in,
            required: $required,
            schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
        );
    }
}
