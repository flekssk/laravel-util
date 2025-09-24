<?php

namespace FKS\Swagger\Attributes\Schemas\Responses;

use Attribute;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use FKS\Swagger\Attributes\Properties\SchemaProperty;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EntityResponse extends Response
{
    public function __construct(string|Schema $schema, string $version = null, string $description = null)
    {
        if (is_a($schema, Schema::class, true)) {
            if (
                is_object($schema)
                && $schema::class === Schema::class
                && $schema->schema === Generator::UNDEFINED
                && is_array($schema->properties)
            ) {
                $dataProperty = new Property(
                    property: 'data',
                    properties: $schema->properties,
                    type: 'object',
                );
            } else {
                $dataProperty = new SchemaProperty(
                    'data',
                    is_object($schema) ? $schema : new $schema(),
                );
            }
        } else {
            $dataProperty = new Property(
                property: 'data',
                ref: '#/components/schemas/' . $schema,
                type: 'object',
            );
        }

        $properties = [];

        $properties[] = new Property(
            property: 'code',
            type: 'string',
            example: 200,
        );
        $properties[] = new Property(
            property: 'status',
            type: 'string',
            example: 'ok',
        );
        $properties[] = $dataProperty;
        if (!empty($version)) {
            $properties[] = new Property(
                property: 'meta',
                type: 'string',
                example: 'api version ' . $version,
            );
        }

        parent::__construct(
            response: 200,
            description: $description ?? 'Entity response',
            content: new JsonContent(
                properties: $properties,
            ),
        );
    }
}
