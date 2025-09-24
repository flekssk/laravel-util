<?php

declare(strict_types=1);

namespace FKS\Metadata\Casts;

use FKS\Metadata\Models\Metadata;
use Illuminate\Database\Eloquent\Model;
use FKS\Metadata\Helpers\MetadataValueHelper;

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
