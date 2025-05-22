<?php

declare(strict_types=1);

namespace FKS\Metadata\Collection;

use Illuminate\Database\Eloquent\Collection;

class MetadataCollection extends Collection
{
    public static function make($items = []): MetadataCollection|\Illuminate\Support\Collection
    {
        return (new static($items))->keyBy('metadata_key');
    }
}