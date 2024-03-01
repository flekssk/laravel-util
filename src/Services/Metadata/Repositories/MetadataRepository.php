<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Repositories;

use Google\Cloud\Spanner\Numeric;
use Illuminate\Database\Eloquent\Builder;
use FKS\Repositories\SearchRepository;
use FKS\Services\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Services\Metadata\Enums\MetadataAggregationValueTypeEnum;
use FKS\Services\Metadata\MetadataConfig;

abstract class MetadataRepository extends SearchRepository
{
    abstract public static function getConfig(): MetadataConfig;

    public function aggregate(
        Builder $builder,
        string $metadataKey,
        MetadataAggregationTypeEnum $aggregation,
        MetadataAggregationValueTypeEnum $valueType
    ): int|float|null {

        $result = $this->buildAggregationQuery($builder, $metadataKey, $aggregation, $valueType)
            ->first()
            ->result;
        if ($result instanceof Numeric) {
            $result = (float) $result->get();
        }

        return $result;
    }

    public function buildAggregationQuery(
        Builder $builder,
        string $metadataKey,
        MetadataAggregationTypeEnum $aggregation,
        MetadataAggregationValueTypeEnum $valueType
    ): Builder {
        $config = static::getConfig();

        $castAs = match($valueType) {
            MetadataAggregationValueTypeEnum::INT => 'INT64',
            MetadataAggregationValueTypeEnum::DECIMAL => 'NUMERIC',
        };

        return $builder->selectRaw("$aggregation->value(SAFE_CAST($config->table.$config->metadataValueFieldName as $castAs)) as result")
            ->join($config->table, "$config->entityTable.$config->entityPrimaryKey", "$config->table.$config->entityPrimaryKey")
            ->whereRaw("$config->table.$config->metadataKeyFieldName = '$metadataKey'");
    }
}
