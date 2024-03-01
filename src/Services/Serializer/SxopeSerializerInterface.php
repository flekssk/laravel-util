<?php

declare(strict_types=1);

namespace FKS\Services\Serializer;

interface FKSSerializerInterface
{
    public function deserializeFromJson(
        string $data,
        string $class,
        array $propertiesMapping = [],
        string $jsonKey = null,
        array $excludedKeys = [],
    ): mixed;

    public function deserializeFromArray(array $data, string $class, array $propertiesMapping = [], array $excludedKeys = []): mixed;
}
