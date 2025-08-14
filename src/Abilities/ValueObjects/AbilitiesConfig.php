<?php

declare(strict_types=1);

namespace FKS\Abilities\ValueObjects;

class AbilitiesConfig
{
    public function __construct(
        public readonly string $facadeClassPath,
    ) {}
}
