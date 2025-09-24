<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Operations;

use Attribute;
use OpenApi\Attributes\Patch as BasePatch;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Patch extends BasePatch
{
    use OperationsTrait;
}
