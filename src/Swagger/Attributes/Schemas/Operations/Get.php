<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Operations;

use Attribute;
use OpenApi\Attributes\Get as BaseGet;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Get extends BaseGet
{
    use OperationsTrait;
}
