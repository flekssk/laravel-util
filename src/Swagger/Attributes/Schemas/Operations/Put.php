<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Operations;

use Attribute;
use OpenApi\Attributes\Put as BasePut;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Put extends BasePut
{
    use OperationsTrait;
}
