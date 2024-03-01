<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Requests;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use FKS\Http\Attributes\Helpers\SchemaExtractorHelper;

class CountsRequest extends RequestBody
{
    use RequestHasFilters;

    /**
     * @param class-string<SearchRequest> $requestClass
     */
    public function __construct(string $requestClass)
    {
        if (is_a($requestClass, \FKS\Http\Requests\SearchRequest::class)) {
            throw new \DomainException(
                'Cant create documentation of counts request, parameter requestClass is not extends SearchRequest'
            );
        }

        $requestReflection = new \ReflectionClass($requestClass);

        $availableFields = $requestClass::getAvailableFields();
        $filters = $this->buildsFiltersProperties($requestClass::getFilteringDefinitions());
        $filters = array_merge($filters, $requestClass::getAdditionalFilterSwaggerProperties());
        $properties = $this->buildCustomizedBuilder($requestClass::getFilteringDefinitions());

        $additionalProperties = SchemaExtractorHelper::convertRulesToProperties(
            $requestReflection->newInstance()->additionalRules(),
        );

        $properties = array_merge(
            $properties,
            $additionalProperties,
        );

        if ($availableFields !== []) {
            $properties[] = new Property(
                property: 'available_fields',
                type: 'array',
                items: new Items(type: 'string'),
                enum: $availableFields,
                example: $availableFields,
            );
        }

        if (!empty($filters)) {
            $properties[] = new Property(
                property: 'filter',
                properties: $filters,
                type: 'object'
            );
        }

        if ($requestClass::getSortingDefinitions() !== []) {
            $properties[] = $this->buildSortingField($requestClass::getSortingDefinitions());
        }

        parent::__construct(
            required: true,
            content: new JsonContent(
                properties: $properties
            ));
    }
}
