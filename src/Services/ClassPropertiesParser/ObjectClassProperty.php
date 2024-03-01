<?php

namespace FKS\Services\ClassPropertiesParser;

class ObjectClassProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $nullable = false,
    ) {
    }
}
