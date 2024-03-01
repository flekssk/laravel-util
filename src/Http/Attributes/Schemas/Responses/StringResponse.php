<?php

namespace FKS\Http\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use FKS\Http\Attributes\Properties\SchemaProperty;

class StringResponse extends Response
{
    public function __construct(string $responseText, string $version = null, string $description = null)
    {
        $properties = [];

        $properties[] = new Property(
            property: 'code',
            type: 'string',
            example: 200
        );
        $properties[] = new SchemaProperty(
            'data',
            new Schema(
                schema: 'StringResponseData',
                properties: [
                    new Property(
                        property: 'status',
                        type: 'string',
                        example: $responseText
                    ),
                ]
            )
        );

        if ($version !== null) {
            $properties[] = new Property(
                property: 'meta',
                type: 'string',
                example: 'api version ' . $version
            );
        }

        parent::__construct(
            response: 200,
            description: $description ?? 'Success',
            content: new JsonContent(
                properties: $properties
            )
        );
    }
}
