<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Operations;

use Attribute;
use OpenApi\Attributes\Post as BasePost;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Post extends BasePost
{
    use OperationsTrait;
}
