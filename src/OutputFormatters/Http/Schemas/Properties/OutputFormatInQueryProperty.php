<?php

declare(strict_types=1);

namespace FKS\OutputFormatters\Http\Schemas\Properties;

use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use FKS\OutputFormatters\Enums\OutputFormatEnum;

class
OutputFormatInQueryProperty extends Parameter
{
    public function __construct()
    {
        parent::__construct(
            name: 'outputFormat',
            in: 'query',
            required: true,
            schema: new Schema(type: 'string', enum: OutputFormatEnum::cases(), example: OutputFormatEnum::HTML->value),
            example: OutputFormatEnum::HTML->value
        );
    }
}
