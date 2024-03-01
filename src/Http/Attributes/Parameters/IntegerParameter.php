<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Parameters;

use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

class IntegerParameter extends Parameter
{
    public function __construct(string $name, string $description = null, string $in = 'path', bool $required = true)
    {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $in,
            required: $required,
            schema: new Schema(type: 'integer', example: 1)
        );
    }
}
