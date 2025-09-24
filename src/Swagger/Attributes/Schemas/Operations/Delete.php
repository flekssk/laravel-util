<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Operations;

use Attribute;
use OpenApi\Attributes\Delete as BaseDelete;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Delete extends BaseDelete
{
    use OperationsTrait;
}
