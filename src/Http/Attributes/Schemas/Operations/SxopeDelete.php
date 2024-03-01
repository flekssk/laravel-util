<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Operations;

use OpenApi\Attributes\Delete as BaseDelete;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FKSDelete extends BaseDelete
{
    use OperationsTrait;
}
