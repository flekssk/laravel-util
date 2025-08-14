<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use FKS\Swagger\Attributes\Properties\ArrayProperty;
use FKS\Swagger\Attributes\Properties\IntegerProperty;
use FKS\Swagger\Attributes\Properties\StringProperty;

class Error500Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 500,
            description: 'Server error',
            content: new JsonContent(
                properties: [
                    new StringProperty('message', 'error'),
                    new StringProperty('exception', 'error'),
                    new StringProperty('file', '/var/www/file.txt'),
                    new IntegerProperty('line', 123),
                ]
            )
        );
    }
}
