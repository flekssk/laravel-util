<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use FKS\Swagger\Attributes\Properties\IntegerProperty;
use FKS\Swagger\Attributes\Properties\ObjectsArrayProperty;
use FKS\Swagger\Attributes\Properties\StringProperty;

class EntityListResponse extends Response
{
    public function __construct(string|Schema $schema, string $apiVersion = null)
    {
        $properties = [
            new IntegerProperty('code', 200),
            new StringProperty('status', 'ok'),
            new ObjectsArrayProperty('data', $schema),
        ];

        if (!empty($apiVersion)) {
            $properties[] = new StringProperty('meta', 'api version ' . $apiVersion);
        }

        parent::__construct(
            response: 200,
            description: 'List response',
            content: new JsonContent(
                properties: $properties            )
        );
    }
}
