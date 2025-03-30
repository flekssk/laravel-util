<?php

declare(strict_types=1);

namespace FKS\Services\Serializer;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed deserializeFromJson(string $data, string $class,array $propertiesMapping = [], string $jsonKey = null, array $excludedKeys = [])
 * @method static mixed deserializeFromArray(array $data, string $class, array $propertiesMapping = [], array $excludedKeys = [], bool $isArrayOf = false)
 * @method static array convertToObjectProperties(string $class, array $data)
 * @method static array serializeToArray(object $object, array $hiddenProperties = [])
 */
class SerializerFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return SerializerInterface::class;
    }
}
