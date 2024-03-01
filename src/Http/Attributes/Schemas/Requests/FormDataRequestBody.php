<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Requests;

use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;

class FormDataRequestBody extends RequestBody
{
    public function __construct(string|Schema $schema)
    {
        if (is_string($schema) && is_a($schema, Schema::class, true)) {
            $schema = new $schema();
            $schema->type = 'object';
        }

        parent::__construct(
            required: true,
            content: [
                new MediaType($schema instanceof Schema ? $schema : new Schema(ref: '#/components/schemas/' . $schema))
            ]
        );
    }
}
