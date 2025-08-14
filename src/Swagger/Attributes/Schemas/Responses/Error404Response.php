<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use FKS\Swagger\Attributes\Properties\StringProperty;

class Error404Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 404,
            description: 'Entity not found',
            content: new JsonContent(
                properties: [
                    new StringProperty('message', 'Model not found.'),
                ]
            )
        );
    }
}
