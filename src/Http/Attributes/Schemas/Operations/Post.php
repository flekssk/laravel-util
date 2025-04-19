<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Operations;

use OpenApi\Attributes\Post as BasePost;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FKSPost extends BasePost
{
    use OperationsTrait;
}
