<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Operations;

use OpenApi\Attributes\Patch as BasePatch;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FKSPatch extends BasePatch
{
    use OperationsTrait;
}
