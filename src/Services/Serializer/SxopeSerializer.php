<?php

declare(strict_types=1);

namespace FKS\Services\Serializer;

use FKS\Services\Serializer\DTO\SerializableDTO;
use Illuminate\Support\Str;

class FKSSerializer implements FKSSerializerInterface
{
    public function deserializeFromJson(
        string $data,
        string $class,
        array $propertiesMapping = [],
        string $jsonKey = null,
        array $excludedKeys = []
    ): mixed {
        $data = json_decode(
            $data,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $this->deserializeFromArray($jsonKey ? $data[$jsonKey] : $data, $class, $propertiesMapping,  $excludedKeys);
    }

    public function deserializeFromArray(
        array $data,
        string $class,
        array $propertiesMapping = [],
        array $excludedKeys = [],
    ): mixed {
        if (is_a($class, SerializableDTO::class, true)) {
            return $class::fromArray($data);
        }

        return new $class(...$data);
    }
}
