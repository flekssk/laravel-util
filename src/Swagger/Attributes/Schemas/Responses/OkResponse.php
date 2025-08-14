<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;

class OkResponse extends Response
{
    public function __construct(string $description = null)
    {
        parent::__construct(
            response: 200,
            description: $description ?? 'Success',
            content: new JsonContent(
                properties: []
            )
        );
    }
}
