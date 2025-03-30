<?php

namespace FKS\Services\ClassPropertiesParser;

readonly class ObjectClassProperty
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable = false,
    ) {
    }
}
