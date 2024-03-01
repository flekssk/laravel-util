<?php

namespace FKS\Http\Attributes\Properties;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

class SchemaProperty extends Property
{
    public string|Schema $elementSchema = Generator::UNDEFINED;

    public function __construct(string $property, Schema $schema, ?bool $deprecated = null)
    {
        $this->elementSchema = $schema;
        parent::__construct(
            property: $property,
            deprecated: $deprecated,
        );
    }

}
