<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Requests;

use DomainException;
use FKS\Search\Helpers\SearchComponentConfigHelper;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use FKS\Metadata\Enums\MetadataFilterTypeEnum;
use FKS\Search\Requests\FilteringDefinitions;
use FKS\Search\Requests\RuleBuilders\ContainsRuleBuilder;
use FKS\Search\Requests\RuleBuilders\DateRangeRuleBuilder;
use FKS\Search\Requests\RuleBuilders\HasEnumFilterValuesInterface;
use FKS\Search\Requests\RuleBuilders\HasSwaggerExampleInterface;
use FKS\Search\Requests\RuleBuilders\MetadataRuleBuilder;
use FKS\Search\Requests\RuleBuilders\NumericRuleBuilder;
use FKS\Search\Requests\RuleBuilders\OneOfEnumRuleBuilder;
use FKS\Search\Requests\RuleBuilders\RuleBuilder;
use FKS\Search\Requests\RuleBuilders\StringSearchRuleBuilder;
use FKS\Search\Requests\SortingRuleBuilders\Enums\SortAsEnum;
use FKS\Swagger\Attributes\Helpers\RequestFiltersHelper;
use FKS\Swagger\Attributes\Properties\ObjectsArrayProperty;
use FKS\Swagger\Attributes\Properties\StringProperty;

trait RequestHasFilters
{
    public function buildsFiltersProperties(FilteringDefinitions $filtersDefinitions): array
    {
        $filters = [];

        /** @var RuleBuilder $filtersDefinition */
        foreach ($filtersDefinitions->toArray() as $filtersDefinition) {
            if ($filtersDefinition->isCustomizedBuilder()) {
                continue;
            }

            $filters[$filtersDefinition->getFilterParam()] = $this->buildProperty($filtersDefinition);
        }

        return $filters;
    }

    public function getRequiredProperties(FilteringDefinitions $filtersDefinitions): array
    {
        $filters = [];

        /** @var RuleBuilder $filtersDefinition */
        foreach ($filtersDefinitions->toArray() as $filtersDefinition) {
            if ($filtersDefinition->isRequired()) {
                $filters[] = $filtersDefinition->getFilterParam();
            }
        }

        return $filters;
    }

    public function buildCustomizedBuilder($filtersDefinitions): array
    {
        $properties = [];
        foreach ($filtersDefinitions->toArray() as $filtersDefinition) {
            if (!$filtersDefinition->isCustomizedBuilder()) {
                continue;
            }

            $properties[] = $this->buildProperty($filtersDefinition);
        }

        return $properties;
    }

    public function buildSortingField(array $getSortingDefinitions): ?Property
    {
        return $getSortingDefinitions !== [] ? new Property(
            property: 'sort',
            type: 'array',
            items: new Items(
                properties: [
                    new Property(
                        property: 'field',
                        type: 'string',
                        enum: $getSortingDefinitions,
                    ),
                    new Property(
                        property: 'direction',
                        type: 'string',
                        enum: ['asc', 'desc'],
                    ),
                    new Property(
                        property: 'sort_as',
                        type: 'string',
                        enum: SortAsEnum::values(),
                        example: SortAsEnum::INTEGER->value,
                    ),
                ],
                type: 'object'
            ),
            example: $this->buildSortingExample($getSortingDefinitions)
        ) : null;
    }

    protected function buildSortingExample(array $getSortingDefinitions): array
    {
        $exampleArray = [];
        foreach ($getSortingDefinitions as $sortingDefinition) {
            $exampleArray[] = [
                'field' => $sortingDefinition,
                "direction" => "asc",
                "sort_as" => 'integer',
            ];
        }
        return $exampleArray;
    }

    public function buildProperty($filtersDefinition): ?Property
    {
        $paramName = $filtersDefinition->getFilterParam();
        $filterType = $filtersDefinition->getSwaggerType();

        if ($filterType === null) {
            foreach (RequestFiltersHelper::$filterBuilderToTypeMap as $typeClass => $type) {
                if (is_a($filtersDefinition, $typeClass, true)) {
                    $filterType = $type;
                }
            }
        }

        if ($filterType === null) {
            $class = $filtersDefinition::class;
            throw new DomainException("filter builder $class to type map doesnt exist");
        }

        $propertyAttributes = [
            'property' => $paramName,
        ];

        if ($filtersDefinition->isDeprecated()) {
            $propertyAttributes['deprecated'] = true;
        }

        if (is_a($filtersDefinition, ContainsRuleBuilder::class)) {
            $properties = [];
            $hasContains = false;
            $hasNotContains = false;
            $example = $this->buildFilterExample($filtersDefinition, $filterType);

            foreach ($filtersDefinition->getRules(SearchComponentConfigHelper::getConfig()->filterParamName) as $rule => $type) {
                if (str_contains($rule, '.contains.')) {
                    $hasContains = true;
                }
                if (
                    $example === null
                    && (str_contains($rule, 'contains.*') || str_contains($rule, 'notcontains.*'))
                ) {
                    if (str_contains($type, 'uuid_or_hex')) {
                        $example = RequestFiltersHelper::$primitivesToExampleMap['uuid_or_hex'];
                    }
                }

                if (str_contains($rule, '.notcontains.')) {
                    $hasNotContains = true;
                }
            }

            if ($hasContains) {
                $itemPropertiesArray = [
                    'type' => $filterType,
                    'example' => $example ?? $filterType,
                ];

                if ($filtersDefinition instanceof HasEnumFilterValuesInterface) {
                    $itemPropertiesArray += ['enum' => $filtersDefinition->getEnumValues()];
                }
                $properties['contains'] = new Property(
                    property: 'contains',
                    type: 'array',
                    items: new Items(
                        ...$itemPropertiesArray
                    )
                );
            }

            if ($hasNotContains) {
                $properties['notcontains'] = new Property(
                    property: 'notcontains',
                    type: 'array',
                    items: new Items(
                        type: $filterType,
                        example: $example ?? $filterType
                    ),
                );
            }
            $propertyAttributes['properties'] = $properties;
            $propertyAttributes['type'] = 'object';
        } elseif (is_a($filtersDefinition, DateRangeRuleBuilder::class)) {
            $properties = [
                new Property(
                    property: 'from',
                    type: $filterType,
                    example: $this->buildFilterExample($filtersDefinition, $filterType)
                ),
                new Property(
                    property: 'to',
                    type: $filterType,
                    example: $this->buildFilterExample($filtersDefinition, $filterType)
                ),
            ];
            $propertyAttributes['properties'] = $properties;
            $propertyAttributes['type'] = 'object';
        } elseif (is_a($filtersDefinition, NumericRuleBuilder::class)) {
            $filterExample = $this->buildFilterExample($filtersDefinition, $filterType);
            $properties = [
                new Property(
                    property: 'eq',
                    type: $filterType,
                    example: $filterExample
                ),
                new Property(
                    property: 'ne',
                    type: $filterType,
                    example: $filterExample
                ),
                new Property(
                    property: 'le',
                    type: $filterType,
                    example: $filterExample
                ),
                new Property(
                    property: 'lt',
                    type: $filterType,
                    example: $filterExample
                ),
                new Property(
                    property: 'gt',
                    type: $filterType,
                    example: $filterExample
                ),
                new Property(
                    property: 'ge',
                    type: $filterType,
                    example: $filterExample
                ),
            ];
            $propertyAttributes['properties'] = $properties;
            $propertyAttributes['type'] = 'object';
        } elseif (is_a($filtersDefinition, OneOfEnumRuleBuilder::class)) {
            $firstCase = $filtersDefinition->getEnumClassString()::cases()[0];
            $propertyAttributes['type'] = $filterType;
            $propertyAttributes['enum'] = $filtersDefinition->getEnumValues();
            $propertyAttributes['example'] = $firstCase->value;
        } elseif (is_a($filtersDefinition, StringSearchRuleBuilder::class)) {
            $propertyAttributes['type'] = $filterType;
            $propertyAttributes['example'] = $paramName;
        } elseif (is_a($filtersDefinition, MetadataRuleBuilder::class)) {
            $propertyAttributes['oneOf'] = [
                new ObjectsArrayProperty(
                    'MetadataInteger',
                    [
                        new StringProperty('filter_type', MetadataFilterTypeEnum::TYPE_INTEGER->value),
                        new StringProperty('metadata_key'),
                        $this->buildProperty(new NumericRuleBuilder('data')),
                    ]
                ),
                new ObjectsArrayProperty(
                    'MetadataContainsHex',
                    [
                        new StringProperty('filter_type', MetadataFilterTypeEnum::TYPE_CONTAINS_HEX->value),
                        new StringProperty('metadata_key'),
                        $this->buildProperty((new ContainsRuleBuilder('data'))->onlyContains()),
                    ]
                ),
                new ObjectsArrayProperty(
                    'MetadataContainsString',
                    [
                        new StringProperty('filter_type', MetadataFilterTypeEnum::TYPE_CONTAINS_STRING->value),
                        new StringProperty('metadata_key'),
                        $this->buildProperty((new ContainsRuleBuilder('data'))->setType('string')->onlyContains()),
                    ]
                ),
                new ObjectsArrayProperty(
                    'MetadataContainsString',
                    [
                        new StringProperty('filter_type', MetadataFilterTypeEnum::TYPE_CONTAINS_STRING->value),
                        new StringProperty('metadata_key'),
                        $this->buildProperty((new StringSearchRuleBuilder('data'))),
                    ]
                ),
            ];
            $propertyAttributes['example'] = [
                [
                    'filter_type' => MetadataFilterTypeEnum::TYPE_CONTAINS_HEX->value,
                    'metadata_key' => 'metadata_key',
                    'data' => [
                        'contains' => [
                            'd46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00'
                        ],
                    ]
                ],
                [
                    'filter_type' => MetadataFilterTypeEnum::TYPE_INTEGER->value,
                    'metadata_key' => 'metadata_key',
                    'data' => [
                        'eq' => 1,
                        'ne' => 1,
                        'le' => 1,
                        'lt' => 1,
                        'gt' => 1,
                        'ge' => 1,
                    ]
                ],
                [
                    'filter_type' => MetadataFilterTypeEnum::TYPE_CONTAINS_STRING->value,
                    'metadata_key' => 'metadata_key',
                    'data' => [
                        'contains' => [
                            'string'
                        ],
                    ]
                ],
                [
                    'filter_type' => MetadataFilterTypeEnum::TYPE_SEARCH_STRING->value,
                    'metadata_key' => 'metadata_key',
                    'data' => 'value'
                ],
            ];
        } else {
            $propertyAttributes['type'] = $filterType;
            $propertyAttributes['example'] = $this->buildFilterExample($filtersDefinition, $filterType);
        }

        return new Property(...$propertyAttributes);
    }

    /**
     * @param RuleBuilder $filtersDefinition
     * @param string|null $filterType
     * @return mixed
     */
    protected function buildFilterExample(RuleBuilder $filtersDefinition, ?string $filterType): mixed
    {
        if (is_a($filtersDefinition, HasSwaggerExampleInterface::class)) {
            return $filtersDefinition->getExample();
        }
        return RequestFiltersHelper::$filterBuilderToExampleMap[$filtersDefinition::class]
            ?? RequestFiltersHelper::$filterBuilderToExampleMap[get_parent_class($filtersDefinition::class) ?? null]
            ?? RequestFiltersHelper::$primitivesToExampleMap[$filterType] ?? null;
    }
}
