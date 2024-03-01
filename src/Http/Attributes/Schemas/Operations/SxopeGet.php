<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Operations;

use OpenApi\Attributes\Get as BaseGet;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FKSGet extends BaseGet
{
    use OperationsTrait;
}
