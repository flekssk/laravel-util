<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\Response;

class Error403Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 403,
            description: 'Not allowed'
        );
    }
}
