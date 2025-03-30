<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\DTO;

readonly class MetadataCreateDTO
{
    public function __construct(
        public int $entityId,
        public string $metadataKey,
        public mixed $metadataValue,
        public int $userId
    ) {
    }
}
