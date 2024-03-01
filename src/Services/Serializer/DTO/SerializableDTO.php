<?php

declare(strict_types=1);

namespace FKS\Services\Serializer\DTO;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use ReflectionClass;
use Serializable;
use FKS\Exceptions\FKSDomainException;
use FKS\Services\ClassPropertiesParser\ArrayClassProperty;
use FKS\Services\ClassPropertiesParser\PropertiesParser;
use FKS\Services\Serializer\ObjectReflector;
use FKS\ValueObjects\Id;

abstract class SerializableDTO implements Serializable, Arrayable
{
    private static function convertToObjectProperties(array $data): array
    {
        $parserProperties = collect(PropertiesParser::getProperties(static::class))->keyBy('name');
        $constructorArgs = [];
        foreach ($parserProperties as $property) {
            $serializedPropertyName = Str::snake($property->name);

            if (!array_key_exists(Str::snake($serializedPropertyName), $data)) {
                continue;
            }

            $value = $data[$serializedPropertyName];

            if (str_contains($property->type, Id::class)) {
                $value = Id::createNullable($value);
            }
            if (is_a($property->type, BackedEnum::class, true)) {
                $value = $property->type::tryFrom($value);
            }
            if (is_a($property->type, self::class, true)) {
                if ($property instanceof ArrayClassProperty) {
                    if ($value === null && !$property->nullable) {
                        throw new FKSDomainException("Property $property->name of class " . static::class . ' can not be nullable');
                    }
                    $value = $value !== null ? array_map(static fn (array $data) => $property->type::fromArray($data), $value) : $value;
                } else {
                    $value = $property->type::fromArray($value);
                }
            }

            $constructorArgs[$property->name] = $value;
        }

        return $constructorArgs;
    }

    public static function fromArray(array $data): self
    {
        return new static(...self::convertToObjectProperties($data));
    }

    public function publicProperties(): ?array
    {
        return null;
    }

    public function toArray(bool $onlyPublic = false): array
    {
        $reflection = new ObjectReflector();
        $properties = $reflection->getProperties($this);

        $array = [];
        foreach ($properties as $name => $property) {
            if (!$onlyPublic || !is_array($this->publicProperties()) || in_array($name, $this->publicProperties())) {
                $array[Str::snake($name)] = $this->serializeProperty($property, $onlyPublic);
            }
        }

        return $array;
    }

    public function serialize(): array
    {
        return $this->toArray();
    }

    public function unserialize(string $data)
    {
        return self::fromArray(unserialize($data));
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data)
    {
        $namedProperties = self::convertToObjectProperties($data);

        foreach ((new ReflectionClass($this))->getProperties() as $property) {
            $property->setValue($this, $namedProperties[$property->getName()]);
        }
    }

    public function cloneAndOverride(array $params): static
    {
        return new static(...array_merge(self::convertToObjectProperties($this->toArray()), $params));
    }

    private function serializeProperty(mixed $property, bool $onlyPublic): mixed
    {
        if ($property instanceof Id) {
            $property = $property->getHex();
        }
        if ($property instanceof self) {
            $property = $property->toArray($onlyPublic);
        }
        if ($property instanceof BackedEnum) {
            $property = $property->value;
        }
        if (is_array($property)) {
            foreach ($property as $index => $item) {
                $property[$index] = $this->serializeProperty($item, $onlyPublic);
            }
        }

        return $property;
    }
}
