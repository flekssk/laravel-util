<?php

declare(strict_types=1);

namespace FKS\Metadata\Collection;

use Illuminate\Database\Eloquent\Collection;

class MetadataCollection extends Collection
{
    public function __get($key)
    {
        return $this->where('metadata_key', $key)->first()?->metadata_value;
    }
}
