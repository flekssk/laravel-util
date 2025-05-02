<?php

declare(strict_types=1);

namespace FKS\Metadata\DTO;

class MetadataValueDTO
{
    public function __construct(
        public readonly string $metadataKey,
        public readonly mixed $metadataValue,
    ) {
    }
}
