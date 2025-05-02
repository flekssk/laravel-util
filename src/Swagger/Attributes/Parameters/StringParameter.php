<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Parameters;

use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

class StringParameter extends Parameter
{
    public function __construct(string $name, string $description = null, string $in = 'path', bool $required = true, string $example = null)
    {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $in,
            required: $required,
            schema: new Schema(type: 'string', example: $example ?? 'string')
        );
    }
}
