<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Models\Traits;

use Colopl\Spanner\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use FKS\Services\Metadata\Models\Metadata;

trait HasMetadata
{
    public function metadata(): HasMany
    {
        if (!is_a($this, Model::class)) {
            throw new \Exception('HasMetadata trait can be applied only to child class of ' . Model::class);
        }

        $model = Metadata::build(static::class);

        return new HasMany(
            $model->newQuery(),
            $this,
            $model->getTable() . '.' . $model->getConfig()->entityPrimaryKey,
            $model->getConfig()->entityPrimaryKey
        );
    }
}
