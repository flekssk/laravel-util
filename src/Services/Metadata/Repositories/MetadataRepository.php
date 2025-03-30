<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use FKS\Repositories\SearchRepository;
use FKS\Services\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Services\Metadata\Enums\MetadataAggregationValueTypeEnum;
use FKS\Services\Metadata\MetadataConfig;
use FKS\Services\Metadata\Models\Metadata;

abstract class MetadataRepository extends SearchRepository
{
    abstract public function getConfig(): MetadataConfig;

    public function aggregate(
        Builder $builder,
        string $metadataKey,
        MetadataAggregationTypeEnum $aggregation,
        MetadataAggregationValueTypeEnum $valueType,
    ): int|float|array|null {
        $query = $this->buildAggregationQuery($builder, $metadataKey, $aggregation, $valueType);

        $result = $aggregation === MetadataAggregationTypeEnum::GROUP_BY_COUNT
            ? $query->get()
            : $query->first()->result;

        $castResult = static function ($value) {
            if (is_string($value)) {
                $value = (float) $value;
            }

            return $value;
        };

        if ($result instanceof Collection) {
            $result->offsetSet('total', $result->sum('result'));
        }

        return $result instanceof Collection ? $result->toArray() : $castResult($result);
    }

    public function buildAggregationQuery(
        Builder $builder,
        string $metadataKey,
        MetadataAggregationTypeEnum $aggregation,
        MetadataAggregationValueTypeEnum $valueType,
    ): Builder {
        $config = $this->getConfig();

        $castAs = match($valueType) {
            MetadataAggregationValueTypeEnum::INT => 'INT64',
            MetadataAggregationValueTypeEnum::DECIMAL => 'NUMERIC',
        };

        if ($aggregation === MetadataAggregationTypeEnum::GROUP_BY_COUNT) {
            $builder->select("$config->table.$config->metadataValueFieldName")
                ->groupBy("$config->table.$config->metadataValueFieldName");

            $aggregation = MetadataAggregationTypeEnum::COUNT;
        }

        $resultSelect = "$config->table.$config->metadataValueFieldName";

        if ($aggregation !== MetadataAggregationTypeEnum::COUNT) {
            $resultSelect = "SAFE_CAST($resultSelect as $castAs)";
        }

        return $builder->selectRaw("$aggregation->value($resultSelect) as result")
            ->whereRaw("$config->table.$config->metadataKeyFieldName = '$metadataKey'")
            ->join($config->table, static function (JoinClause $join) use ($config) {
                $join->on("$config->entityTable.$config->entityPrimaryKey", "$config->table.$config->entityPrimaryKey");
            });
    }

    public function getQuery(): Builder
    {
        $model = Metadata::build($this->getConfig());

        return $this->isSoftDeleteExpected
            ? $model::query()
            : ($this->hasSoftDeletedTrait ? $model::withTrashed() : $model::query());
    }
}
