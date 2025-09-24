<?php

namespace FKS\Swagger\Attributes;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

class SchemaItems extends Items
{
    public Schema|string $elementSchema = Generator::UNDEFINED;

    public function __construct(Schema $schema)
    {
        $this->elementSchema = $schema;

        parent::__construct();
    }
}
