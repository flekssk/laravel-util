<?php

declare(strict_types=1);

namespace FKS\Services\Serializer;

use Illuminate\Support\Str;
use FKS\Services\Serializer\Elements\ArrayItem;

class FKSSimpleSerializer implements FKSSerializerInterface
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
        array $excludedKeys = []
    ): mixed {

        $data = self::convertArrayKeysToCamelCase($data);

        foreach ($excludedKeys as $excludedKey) {
            unset($data[$excludedKey]);
        }

        foreach ($propertiesMapping as $propertyName => $property) {
            $propertyValue = $property instanceof ArrayItem ? $data : $data[$propertyName] ?? null;

            if ($propertyValue !== null) {
                if ($property instanceof ArrayItem) {
                    $resultArray = [];

                    foreach ($propertyValue as $item) {
                        $resultArray[] = $this->deserializeFromArray(
                            $item,
                            $property->class,
                            $property->propertiesMapping ?? [],
                            $excludedKeys
                        );
                    }
                    $data[$propertyName] = $resultArray;
                } elseif (is_array($property)) {
                    $resultArray = [];
                    foreach ($data[$propertyName] as $item) {
                        $resultArray[] = new $property[0](...$item);
                    }
                    $data[$propertyName] = $resultArray;
                } elseif (is_string($property)) {
                    $data[$property] = $data[$propertyName];
                    unset($data[$propertyName]);
                } else {
                    $data[$propertyName] = new $property(...$data[$propertyName]);
                }
            }
        }

        return new $class(...$data);
    }

    public static function convertArrayKeysToCamelCase(array $data): array
    {
        return array_combine(
            array_map([Str::class, 'camel'], array_keys($data)),
            $data
        );
    }
}
