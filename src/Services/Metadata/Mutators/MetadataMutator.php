<?php

namespace FKS\Services\Metadata\Mutators;

use Illuminate\Support\Arr;

abstract class MetadataMutator implements MetadataMutatorInterface
{
    public static array $allowedMetadata = [];

    public function apply(string $metadataKey, mixed $metadataValue): mixed
    {
        $method = static::$allowedMetadata[$metadataKey] ?? null;

        if ($method !== null) {
            $metadataValue = $this->$method($metadataValue);
        }

        return $metadataValue;
    }
}
