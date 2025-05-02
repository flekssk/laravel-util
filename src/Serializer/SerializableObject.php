<?php

declare(strict_types=1);

namespace FKS\Serializer;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use Serializable as SerializableInterface;

abstract class SerializableObject implements SerializableInterface, Arrayable
{
    public static function fromArray(array $data): self
    {
        return SerializerFacade::deserializeFromArray($data, static::class);
    }

    public function toArray(): array
    {
        return SerializerFacade::serializeToArray($this);
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
        $namedProperties = SerializerFacade::convertToObjectProperties(static::class, $data);

        foreach ((new ReflectionClass($this))->getProperties() as $property) {
            $property->setValue($this, $namedProperties[$property->getName()]);
        }
    }

    public function cloneAndOverride(array $params): static
    {
        return new static(...array_merge(SerializerFacade::convertToObjectProperties(static::class, $this->toArray()), $params));
    }
}
