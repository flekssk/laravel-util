<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Casts;

use Illuminate\Database\Eloquent\Model;
use FKS\Services\Metadata\Helpers\MetadataValueHelper;
use FKS\Services\Metadata\Models\Metadata;

class MetadataValueCast
{
    public function get(Model|Metadata $model, string $key, mixed $value, array $attributes): mixed
    {

        return MetadataValueHelper::applyMutators(
            $model->getConfig(),
            $attributes[$model->getConfig()->metadataKeyFieldName],
            MetadataValueHelper::fromString($value)
        );
    }

    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes
    ): mixed {
        return MetadataValueHelper::toString($value);
    }
}
