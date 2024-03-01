<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use FKS\Http\Attributes\Properties\ArrayProperty;
use FKS\Http\Attributes\Properties\IntegerProperty;
use FKS\Http\Attributes\Properties\ObjectsArrayProperty;
use FKS\Http\Attributes\Properties\StringProperty;

class Error422Response extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 422,
            description: 'Validation error',
            content: new JsonContent(
                properties: [
                    new IntegerProperty('code', 422),
                    new StringProperty('status', 'error'),
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
