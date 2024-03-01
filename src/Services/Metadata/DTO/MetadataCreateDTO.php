<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\DTO;

use Google\Cloud\Spanner\Bytes;

class MetadataCreateDTO
{
    public function __construct(
        public readonly Bytes $entityId,
        public readonly string $metadataKey,
        public readonly mixed $metadataValue,
        public readonly Bytes $userId
    ) {
    }
}
