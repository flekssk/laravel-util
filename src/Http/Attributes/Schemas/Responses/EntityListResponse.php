<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Responses;

use OpenApi\Attributes\Schema;
use FKS\Http\Attributes\Properties\IntegerProperty;
use FKS\Http\Attributes\Properties\ObjectsArrayProperty;
use FKS\Http\Attributes\Properties\StringProperty;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;

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
