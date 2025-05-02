<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use DomainException;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use FKS\Search\Requests\SearchRequest;
use FKS\Swagger\Attributes\Properties\IntegerProperty;
use FKS\Swagger\Attributes\Properties\ObjectsArrayProperty;
use FKS\Swagger\Attributes\Properties\StringProperty;

class CountsResponse extends Response
{
    /**
     * @param class-string<SearchRequest> $countRequestClass
     */
    public function __construct(string $countRequestClass, string $apiVersion)
    {
        if (!is_a($countRequestClass, SearchRequest::class, true)) {
            throw new DomainException('countRequestClass param must be CountsRequest class string or object');
        }

        $countRequest = $countRequestClass::getAvailableFields();

        if (!empty($countRequest)) {
            $groupProperties = [
                new StringProperty($countRequest[0], enum: $countRequest),
                new IntegerProperty('count'),
            ];
        }

        $properties = [
            new IntegerProperty('total_count'),
            new ObjectsArrayProperty(
                'list',
                $groupProperties ?? []
            )
        ];

        $dataProperty = new Property(
            property: 'data',
            properties: $properties,
            type: 'object'
        );

        parent::__construct(
            response: 200,
            description: 'List response',
            content: new JsonContent(
                properties: [
                    new IntegerProperty('code', 200),
                    new StringProperty('status', 'ok'),
                    $dataProperty,
                    new StringProperty('meta', 'api version ' . $apiVersion)
                ]
            )
        );
    }
}
