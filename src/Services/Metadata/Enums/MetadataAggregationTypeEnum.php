<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Enums;

enum MetadataAggregationTypeEnum: string
{
    case SUM = 'SUM';
    case AVG = 'AVG';
    case MAX = 'MAX';
    case MIN = 'MIN';
}
