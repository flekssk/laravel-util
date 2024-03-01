<?php

declare(strict_types=1);

namespace FKS\Services\ClassPropertiesParser;

class ArrayClassProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $nullable = false,
    ) {
    }
}
