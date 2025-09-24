<?php

declare(strict_types=1);

namespace FKS\Metadata\Enums;

use FKS\Enums\MappedEnum;

enum MetadataFilterTypeEnum: string
{
    use MappedEnum;

    case TYPE_CONTAINS_HEX = 'contains_hex';
    case TYPE_CONTAINS_STRING = 'contains_string';
    case TYPE_INTEGER = 'integer';
    case TYPE_FLOAT = 'float';
    case TYPE_SEARCH_STRING = 'search_string';
    case TYPE_DATE = 'date';
    case TYPE_TIMESTAMP = 'datetime';
    case TYPE_CONTAINS_INTEGER = 'contains_integer';
    case TYPE_CONTAINS_BOOLEAN = 'contains_boolean';
    case TYPE_CONTAINS_STRING_IN_ARRAY = 'contains_string_in_array';
    case TYPE_CONTAINS_INTEGER_IN_ARRAY = 'contains_integer_in_array';

    public function isContains(): bool
    {
        return in_array(
            $this,
            [
                self::TYPE_CONTAINS_STRING,
                self::TYPE_CONTAINS_HEX,
                self::TYPE_CONTAINS_INTEGER,
                self::TYPE_CONTAINS_BOOLEAN,
                self::TYPE_CONTAINS_STRING_IN_ARRAY,
                self::TYPE_CONTAINS_INTEGER_IN_ARRAY,
            ]
        );
    }

    public function isDate(): bool
    {
        return in_array(
            $this,
            [
                self::TYPE_DATE,
                self::TYPE_TIMESTAMP,
            ]
        );
    }

    public function isStringArray(): bool
    {
        return in_array(
            $this,
            [
                self::TYPE_CONTAINS_STRING_IN_ARRAY,
                self::TYPE_CONTAINS_INTEGER_IN_ARRAY,
            ]
        );
    }
}
