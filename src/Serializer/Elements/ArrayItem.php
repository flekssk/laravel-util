<?php

declare(strict_types=1);

namespace FKS\Serializer\Elements;

class ArrayItem
{
    public function __construct(public string $class, public ?array $propertiesMapping = null)
    {
    }

    public static function create(string $class, ?array $propertiesMapping = null): ArrayItem
    {
        return new self($class, $propertiesMapping);
    }
}
