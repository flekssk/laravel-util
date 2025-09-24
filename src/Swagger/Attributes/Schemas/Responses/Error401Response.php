<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use FKS\Swagger\Attributes\Properties\StringProperty;

class Error401Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 401,
            description: 'Unauthenticated',
            content: new JsonContent(
                properties: [
                    new StringProperty('message', 'Unauthenticated.'),
                ]
            )
        );
    }
}
