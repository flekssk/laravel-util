<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Properties;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use FKS\Http\Attributes\SchemaItems;

class ObjectsArrayProperty extends Property
{
    public function __construct(
        string $name,
        array|string|Schema $objectProperties,
        ?bool $deprecated = null,
        ?array $required = null,
    )
    {
        if (is_array($objectProperties)) {
            $items = new Items(properties: $objectProperties, type: 'object');
        } elseif (is_a($objectProperties, Schema::class, true)) {
            if (
                is_object($objectProperties) &&
                $objectProperties::class === Schema::class &&
                $objectProperties->schema === Generator::UNDEFINED
            ) {
                $items = new Items(properties: $objectProperties->properties, type: 'object');
            } else {
                $items = new SchemaItems(is_object($objectProperties) ? $objectProperties : new $objectProperties());
            }
        } else {
            $items = new Items(ref: '#/components/schemas/' . $objectProperties);
        }

        parent::__construct(
            property: $name,
            type: 'array',
            items: $items,
            deprecated: $deprecated,
            required: $required,
        );
    }
}
