<?php

declare(strict_types=1);

namespace FKS\Serializer;

interface SerializerInterface
{
    public function deserializeFromJson(
        string $data,
        string $class,
        array $propertiesMapping = [],
        string $jsonKey = null,
        array $excludedKeys = [],
    ): mixed;

    public function deserializeFromArray(array $data, string $class, array $propertiesMapping = [], array $excludedKeys = [], bool $isArrayOf = false): mixed;
    public function convertToObjectProperties(string $class, array $data): array;
    public function serializeToArray(object $object, array $hiddenProperties = []): array;
}
