<?php

declare(strict_types=1);

namespace FKS\Metadata\DTO;

class MetadataCreateDTO
{
    public function __construct(
        public readonly int $entityId,
        public readonly string $metadataKey,
        public readonly mixed $metadataValue,
        public readonly int $userId
    ) {
    }
}
