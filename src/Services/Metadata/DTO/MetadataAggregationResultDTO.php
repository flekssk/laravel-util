<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\DTO;

use FKS\Services\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Services\Metadata\Enums\MetadataAggregationValueTypeEnum;
use FKS\Services\Serializer\DTO\SerializableDTO;

class MetadataAggregationResultDTO extends SerializableDTO
{
    public function __construct(
        public string $metadataKey,
        public MetadataAggregationTypeEnum $aggregationType,
        public MetadataAggregationValueTypeEnum $valueType,
        public int|float|null $result,
    ) {
    }
}
