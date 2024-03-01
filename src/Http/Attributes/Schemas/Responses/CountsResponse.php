<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Responses;

use FKS\Http\Attributes\Properties\IntegerProperty;
use FKS\Http\Attributes\Properties\ObjectsArrayProperty;
use FKS\Http\Attributes\Properties\StringProperty;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use FKS\Exceptions\FKSDomainException;
use FKS\Http\Requests\SearchRequest;

class CountsResponse extends Response
{
    /**
     * @param class-string<SearchRequest> $countRequestClass
     */
    public function __construct(string $countRequestClass, string $apiVersion)
    {
        if (!is_a($countRequestClass, SearchRequest::class, true)) {
            throw new FKSDomainException('countRequestClass param must be CountsRequest class string or object');
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
