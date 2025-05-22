<?php

declare(strict_types=1);

namespace FKS\Metadata\Models\Traits;

use App\PaymentsGateways\ValueObjects\PaymentContext;
use Exception;
use FKS\Metadata\Models\Metadata;
use FKS\Serializer\SerializerFacade;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public function updateMetadataValue(string $key, mixed $value): void
    {
        if (!$this->metadata->has($key)) {
            $this->metadata->put($key, $this->makeMetadata($key, $value));
        }

        $this->metadata->get($key)
            ->{MetadataConfigHelper::getModelConfig(static::class)->metadataValueFieldName} = $value;
    }

    public function getOrCreateMetdata(string $key, mixed $defaultValue = null): mixed
    {
        if (!$this->metadata->has($key)) {
            $this->metadata->put($key, $this->makeMetadata($key, $defaultValue));
        }

        return $this->metadata->get($key);
    }

    public function makeMetadata(string $key, mixed $value): Metadata
    {
        $model = Metadata::build(MetadataConfigHelper::getModelConfig(static::class));

        $model->{$model->getConfig()->metadataKeyFieldName} = $key;
        $model->{$model->getConfig()->metadataValueFieldName} = $value;

        return $model;
    }

    /**
     * @param class-string|null $castAs
     */
    public function makeMetadataAttrubuteCast(string $key, string $castAs = null, mixed $default = null): Attribute
    {
        if ($castAs !== null && !class_exists($castAs)) {
            throw new Exception('Cast class ' . $castAs . ' not found.');
        }

        $config = MetadataConfigHelper::getModelConfig(static::class);

        return Attribute::make(
            get: function () use ($default, $config, $key, $castAs) {
                $metadataValue = $this->getOrCreateMetdata(
                    $key,
                    $castAs ? SerializerFacade::serializeToArray($default) : $default
                )->{$config->metadataValueFieldName};

                SerializerFacade::deserializeFromArray($metadataValue, $castAs);

                return $castAs !== null
                    ? SerializerFacade::deserializeFromArray($metadataValue, $castAs)
                    : $this->getOrCreateMetdataValue($key);
            },
            set: fn (mixed $value) => $this->updateMetadataValue(
                $key, $castAs !== null
                    ? SerializerFacade::serializeToArray($value)
                    : $value
            )
        );
    }
}
