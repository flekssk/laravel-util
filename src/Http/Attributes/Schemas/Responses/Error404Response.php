<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Responses;

use OpenApi\Attributes\Response;

class Error404Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 404,
            description: 'Entity not found'
        );
    }
}
