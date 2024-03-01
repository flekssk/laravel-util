<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\DTO;

class MetadataValueDTO
{
    public function __construct(
        public readonly string $taskMetadataKey,
        public readonly mixed $taskMetadataValue,
    ) {
    }
}
