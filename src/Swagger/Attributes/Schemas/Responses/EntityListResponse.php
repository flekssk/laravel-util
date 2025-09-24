<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use FKS\Search\Swagger\CountsSchema;
use FKS\Swagger\Attributes\Properties\ObjectsArrayProperty;
use FKS\Swagger\Attributes\Properties\SchemaProperty;

class EntityListResponse extends Response
{
    public function __construct(string|Schema $schema, bool $withCounts = true, bool $withPagination = true)
    {
        $properties = [
            new ObjectsArrayProperty('list', $schema),
        ];

        if ($withCounts) {
            $properties[] = new SchemaProperty('count', new CountsSchema());
        }

        parent::__construct(
            response: 200,
            description: 'List response',
            content: new JsonContent(
                properties: $properties
            )
        );
    }
}
