<?php

declare(strict_types=1);

namespace FKS\Abilities\ValueObjects;

class AbilityMethod
{
    /**
     * @param \ReflectionParameter[] $methodParameters
     */
    public function __construct(
        public readonly string $methodName,
        public readonly array $methodParameters,
        public readonly \ReflectionType $methodReturnType,
    ) {}
}
