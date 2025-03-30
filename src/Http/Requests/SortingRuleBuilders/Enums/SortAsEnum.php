<?php

declare(strict_types=1);

namespace FKS\Http\Requests\SortingRuleBuilders\Enums;

use FKS\Enums\MappedEnum;

enum SortAsEnum: string
{
    use MappedEnum;

    case INTEGER = 'integer';
    case FLOAT = 'float';
    case TIMESTAMP = 'timestamp';
}
