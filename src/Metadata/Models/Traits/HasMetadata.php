<?php

declare(strict_types=1);

namespace FKS\Metadata\Models\Traits;

use Exception;
use FKS\Metadata\Models\Metadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use FKS\Metadata\Helpers\MetadataConfigHelper;

trait HasMetadata
{
    public function metadata(): HasMany
    {
        if (!is_a($this, Model::class)) {
            throw new Exception('HasMetadata trait can be applied only to child class of ' . Model::class);
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
