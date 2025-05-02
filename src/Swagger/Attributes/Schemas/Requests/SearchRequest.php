<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Requests;

use L5Swagger\Exceptions\L5SwaggerException;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use Throwable;

class SearchRequest extends RequestBody
{
    use RequestHasFilters;

    /**
     * @param class-string<\FKS\Search\Requests\SearchRequest> $requestClass
     */
    public function __construct(string $requestClass, array $additionalProperties = [])
    {
        if (is_a($requestClass, \FKS\Search\Requests\SearchRequest::class)) {
            throw new L5SwaggerException(
                'Can`t create documentation of list request, parameter requestClass is not extends SearchRequest'
            );
        }

        try {
            $availableFields = $requestClass::getAvailableFields();
            $sortingFields = $this->buildSortingField($requestClass::getSortingDefinitions());
            $filters = $this->buildsFiltersProperties($requestClass::getFilteringDefinitions());
            $requiredFilters = $this->getRequiredProperties($requestClass::getFilteringDefinitions());
            $filters = array_merge($filters, $requestClass::getAdditionalFilterSwaggerProperties());
            $properties = $this->buildCustomizedBuilder($requestClass::getFilteringDefinitions());

            $requestProperties = [
                new Property(
                    property: 'available_fields',
                    type: 'array',
                    items: new Items(
                        type: 'string',
                        enum: $availableFields,
                    ),
                    example: $availableFields,
                ),
            ];
            if (!empty($filters)) {
                $requestProperties[] = new Property(
                    property: 'filter',
                    required: count($requiredFilters) ? $requiredFilters : null,
                    properties: $filters,
                    type: 'object'
                );
            }
            if (!empty($sortingFields)) {
                $requestProperties[] = $sortingFields;
            }

            parent::__construct(
                required: true,
                content: new JsonContent(
                    properties: array_merge(
                        $requestProperties,
                        $properties,
                        $additionalProperties,
                    )
                )
            );
        } catch (Throwable $e) {
            throw new L5SwaggerException($e->getMessage());
        }
    }
}
