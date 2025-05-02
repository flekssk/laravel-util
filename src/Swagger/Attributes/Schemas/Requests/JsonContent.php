<?php

namespace FKS\Swagger\Attributes\Schemas\Requests;

use OpenApi\Attributes\Schema;
use OpenApi\Generator;

class JsonContent extends \OpenApi\Attributes\JsonContent
{
    public string|Schema $elementSchema = Generator::UNDEFINED;
}
