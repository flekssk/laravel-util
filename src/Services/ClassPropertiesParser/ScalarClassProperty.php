<?php

declare(strict_types=1);

namespace FKS\Services\ClassPropertiesParser;

class ScalarClassProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $nullable = false,
    ) {
    }
}
