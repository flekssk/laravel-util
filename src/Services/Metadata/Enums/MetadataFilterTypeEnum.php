<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Enums;

use FKS\Enums\MappedEnum;

enum MetadataFilterTypeEnum: string
{
    use MappedEnum;

    case TYPE_CONTAINS_HEX = 'contains_hex';
    case TYPE_CONTAINS_STRING = 'contains_string';
    case TYPE_INTEGER = 'integer';
    case TYPE_SEARCH_STRING = 'search_string';

    public function isContains(): bool
    {
        return in_array(
            $this,
            [
                self::TYPE_CONTAINS_STRING,
                self::TYPE_CONTAINS_HEX,
            ]
        );
    }
}
