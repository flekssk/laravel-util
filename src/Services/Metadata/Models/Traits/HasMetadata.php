<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use FKS\Services\Metadata\Helpers\MetadataConfigHelper;
use FKS\Services\Metadata\Models\Metadata;

trait HasMetadata
{
    public function metadata(): HasMany
    {
        if (!is_a($this, Model::class)) {
            throw new \Exception('HasMetadata trait can be applied only to child class of ' . Model::class);
        }

        $model = Metadata::build(MetadataConfigHelper::getModelConfig(static::class));

        $toSelect = ['*'];

        if ($model->getConfig()->onlyMetadataKeys) {
            $toSelect = [
                $model->getConfig()->primaryKey,
                $model->getConfig()->entityPrimaryKey,
                $model->getConfig()->metadataKeyFieldName,
                $model->getConfig()->metadataValueFieldName,
            ];
        }

        return (new HasMany(
            $model->newQuery(),
            $this,
            $model->getTable() . '.' . $model->getConfig()->entityPrimaryKey,
            $model->getConfig()->entityPrimaryKey
        ))->select($toSelect);
    }
}
