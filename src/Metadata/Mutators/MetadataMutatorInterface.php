<?php

declare(strict_types=1);

namespace FKS\Metadata\Mutators;

interface MetadataMutatorInterface
{
    public function apply(string $metadataKey, mixed $metadataValue): mixed;
}
