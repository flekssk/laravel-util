<?php

declare(strict_types=1);

namespace FKS\Metadata\DTO;

use FKS\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Metadata\Enums\MetadataAggregationValueTypeEnum;
use FKS\Serializer\SerializableObject;

class MetadataAggregationResultDTO extends SerializableObject
{
    public function __construct(
        public string $metadataKey,
        public MetadataAggregationTypeEnum $aggregationType,
        public MetadataAggregationValueTypeEnum $valueType,
        public int|float|array|null $result,
    ) {
    }
}
