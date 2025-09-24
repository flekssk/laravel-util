<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use FKS\Swagger\Attributes\Properties\ArrayProperty;
use FKS\Swagger\Attributes\Properties\IntegerProperty;
use FKS\Swagger\Attributes\Properties\ObjectsArrayProperty;
use FKS\Swagger\Attributes\Properties\StringProperty;

class Error422Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 422,
            description: 'Validation error',
            content: new JsonContent(
                properties: [
                    new StringProperty('message', 'error'),
                    new ObjectsArrayProperty(
                        'errors',
                        [
                            new ArrayProperty(
                                'invalidate_field',
                                'string',
                                'Field validation error'
                            )
                        ]
                    ),
                ]
            )
        );
    }
}
