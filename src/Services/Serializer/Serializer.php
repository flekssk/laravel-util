<?php

declare(strict_types=1);

namespace FKS\Services\Serializer;

use BackedEnum;
use Illuminate\Support\Arr;
use FKS\Services\ClassPropertiesParser\ArrayClassProperty;
use FKS\Services\ClassPropertiesParser\PropertiesParser;
use Illuminate\Support\Str;
use FKS\Services\ClassPropertiesParser\ScalarClassProperty;
use FKS\ValueObjects\Id;

class Serializer implements SerializerInterface
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
        bool $isArrayOf = false
    ): mixed {
        $result = null;
        if ($isArrayOf) {
            $result = [];
            foreach ($data as $datum) {
                $result[] = new $class(...$this->convertToObjectProperties($class, $datum));
            }
        } else {
            $result = new $class(...$this->convertToObjectProperties($class, $data));
        }

        return $result;
    }

    public function convertToObjectProperties(string $class, array $data): array
    {
        $parserProperties = collect(PropertiesParser::getProperties($class))->keyBy('name');
        $constructorArgs = [];
        /** @var ArrayClassProperty|ScalarClassProperty $property */
        foreach ($parserProperties as $property) {
            $serializedPropertyName = Str::snake($property->name);

            $value = $data[$serializedPropertyName] ?? null;
            if (!array_key_exists(Str::snake($serializedPropertyName), $data) &&
                $property->nullable && $this->isCastable($value)
            ) {
                continue;
            }

            if (is_a($property->type, Id::class, true)) {
                $value = $property instanceof ArrayClassProperty ? Id::batchCreate($value) : Id::createNullable($value);
            } elseif (is_a($property->type, BackedEnum::class, true)) {
                $value = is_a($value, $property->type) ? $value : $property->type::tryFrom($value);
            } elseif (class_exists($property->type)) {
                if ($property instanceof ArrayClassProperty) {
                    if ($value === null && !$property->nullable) {
                        throw new \DomainException("Property $property->name of class " . static::class . ' can not be nullable');
                    }
                    $value = $value !== null ? array_map(fn (array $data) => $this->deserializeFromArray($data, $property->type), $value) : $value;
                } else {
                    $value = $this->deserializeFromArray($value, $property->type);
                }
            }

            if (
                $this->isCastable($value, $property)
                && $this->needsCasting($value, $property)
            ) {
                if (in_array($this->matchTypes($property->type), $this->getCastableTypes())) {
                    settype($value, $this->matchTypes($property->type));
                }
            }

            $constructorArgs[$property->name] = $value;
        }

        return $constructorArgs;
    }

    public function serializeToArray(object $object, array $hiddenProperties = []): array
    {
        $reflection = new ObjectReflector();
        $properties = $reflection->getProperties($object);

        $array = [];

        foreach ($properties as $name => $property) {
            if (!in_array($name, $hiddenProperties)) {
                $array[Str::snake($name)] = $this->serializeProperty($property, Arr::get($hiddenProperties, $name, []));
            }
        }

        return $array;
    }

    private function serializeProperty(mixed $property, array $hiddenProperties = []): mixed
    {
        if ($property instanceof Id) {
            return $property->getHex();
        } elseif ($property instanceof SerializableObject) {
            return $property->toArray();
        } elseif ($property instanceof BackedEnum) {
            return $property->value;
        } elseif (is_object($property)) {
            $property = $this->serializeToArray($property, $hiddenProperties);
        } elseif (is_array($property)) {
            foreach ($property as $index => $item) {
                $property[$index] = $this->serializeProperty($item, $hiddenProperties);
            }

            return $property;
        }

        return $property;
    }

    public function matchTypes($type) {
        return match(strtolower($type)) {
            'array' => 'array',
            'object' => 'object',
            'bool' => 'boolean',
            'int' => 'integer',
            'float' => 'float',
            'string' => 'string',
            'mixed' => 'mixed',
            'integer' => 'integer',
            default => $type
        };

    }

    private function isCastable($value, $property = null): bool
    {
        return is_scalar($value) || (is_null($value) && $property?->nullable === false);
    }

    private function getCastableTypes(): array
    {
        return ["bool", "boolean", "int", "integer", "float", "double", "string", "array", "object", "null"];
    }


    private function needsCasting($value, $property): bool
    {
        return gettype($value) !== $this->matchTypes($property->type) && !$property->nullable;
    }
}
