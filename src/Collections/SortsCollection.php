<?php

declare(strict_types=1);

namespace FKS\Collections;

use Illuminate\Support\Collection;

class SortsCollection extends Collection
{
    public function hasSort(string $name): bool
    {
        return $this->first(static fn ($sort) => $sort['field'] === $name) !== null;
    }
    
    public function removeSort(string $name): bool
    {
        return $this->first(static fn ($sort) => $sort['field'] === $name) !== null;
    }
}
