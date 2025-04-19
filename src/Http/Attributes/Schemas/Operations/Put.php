<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Operations;

use OpenApi\Attributes\Put as BasePut;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Put extends BasePut
{
    use OperationsTrait;
}
