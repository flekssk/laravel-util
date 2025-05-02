<?php

declare(strict_types=1);

namespace FKS\Metadata;

use FKS\Metadata\Mutators\MetadataMutatorInterface;
use FKS\Serializer\SerializableObject;

class MetadataConfig extends SerializableObject
{
    /**
     * @param MetadataMutatorInterface[] $mutators
     */
    public function __construct(
        public readonly string $table,
        public readonly string $primaryKey,
        public readonly string $entityTable,
        public readonly string $entityPrimaryKey,
        public readonly string $metadataKeyFieldName,
        public readonly string $metadataValueFieldName,
        public readonly bool $storeValueAsJson = false,
        public bool $onlyMetadataKeys = false,
        public readonly array $mutators = [],
    ) {
    }
}
