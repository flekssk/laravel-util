<?php

declare(strict_types=1);

namespace FKS\Services\ClassPropertiesParser;

readonly class ArrayClassProperty
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable = false,
    ) {
    }
}
