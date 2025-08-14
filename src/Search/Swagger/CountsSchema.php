<?php

declare(strict_types=1);

namespace FKS\Search\Swagger;

use OpenApi\Attributes\Schema;
use FKS\Swagger\Attributes\Properties\IntegerProperty;

class CountsSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new IntegerProperty('found'),
                new IntegerProperty('all'),
            ]
        );
    }
}
