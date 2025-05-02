<?php

declare(strict_types=1);

namespace FKS\Metadata\Enums;

enum MetadataAggregationTypeEnum: string
{
    case SUM = 'SUM';
    case AVG = 'AVG';
    case MAX = 'MAX';
    case MIN = 'MIN';
    case COUNT = 'COUNT';
    case GROUP_BY_COUNT = 'GROUP_BY_COUNT';
}
