<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use FKS\Http\Attributes\Properties\IntegerProperty;
use FKS\Http\Attributes\Properties\StringProperty;
use FKS\Http\Attributes\Properties\ArrayProperty;

class Error500Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 500,
            description: 'Server error',
            content: new JsonContent(
                properties: [
                    new IntegerProperty('code', 500),
                    new StringProperty('status', 'error'),
                    new ArrayProperty('data', 'string', 'Server error'),
                ]
            )
        );
    }
}
