<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\DTO;

readonly class MetadataValueDTO
{
    public function __construct(
        public string $metadataKey,
        public mixed $metadataValue,
    ) {
    }
}
