<?php

declare(strict_types=1);

namespace FKS\Services\Metadata;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use FKS\Facades\FKS;
use FKS\Services\Metadata\DTO\MetadataAggregationResultDTO;
use FKS\Services\Metadata\DTO\MetadataCreateDTO;
use FKS\Services\Metadata\DTO\MetadataValueDTO;
use FKS\Services\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Services\Metadata\Enums\MetadataAggregationValueTypeEnum;
use FKS\Services\Metadata\Helpers\FKSMetadataConfigHelper;
use FKS\Services\Metadata\Models\Metadata;
use FKS\Services\Metadata\Repositories\MetadataRepository;
use FKS\ValueObjects\Id;

/**
 * @template ModelClass
 */
abstract class MetadataService
{
    public MetadataRepository $repository;

    abstract public static function getEntity(): string;

    public function __construct()
    {
        $this->repository = $this->buildRepository();
    }

    public function getConfig(): MetadataConfig
    {
        return FKSMetadataConfigHelper::getModelConfig(static::getEntity());
    }

    /**
     * @param MetadataValueDTO[] $states
     */
    public function getByMetadataValue(array $states): Collection
    {
        $query = $this->buildQueryByMetadataValue($states);
        $query->orderBy('created_at', 'desc');

        return $query->get();
    }

    public function upsertMetadataChunk(Id $entityId, array $metadataValues, Id $userId): void
    {
        foreach ($metadataValues as $metadataKey => $metadataValue) {
            $this->upsertMetadata(
                new MetadataCreateDTO(
                    $entityId,
                    $metadataKey,
                    $metadataValue,
                    $userId,
                )
            );
        }
    }

    public function upsertMetadata(MetadataCreateDTO $metadataCreateDTO): Metadata
    {
        $config = $this->getConfig();
        return $this->repository->updateOrCreate(
            [
                $config->entityPrimaryKey => $metadataCreateDTO->entityId,
                $config->metadataKeyFieldName => $metadataCreateDTO->metadataKey,
            ],
            [
                $config->metadataValueFieldName => $metadataCreateDTO->metadataValue,
            ],
            [
                'created_at_day_id' => FKS::getCurrentDayId(),
                'created_at' => Carbon::now('UTC')->format('c'),
                'created_by' => $metadataCreateDTO->userId,
            ],
            [
                'updated_at_day_id' => FKS::getCurrentDayId(),
                'updated_at' => Carbon::now('UTC')->format('c'),
                'updated_by' => $metadataCreateDTO->userId,
            ]
        );
    }

    public function aggregate(
        Builder $builder,
        string $metadataKey,
        MetadataAggregationTypeEnum $aggregation,
        MetadataAggregationValueTypeEnum $valueType
    ): MetadataAggregationResultDTO {
        return new MetadataAggregationResultDTO(
            $metadataKey,
            $aggregation,
            $valueType,
            $this->repository->aggregate($builder, $metadataKey, $aggregation, $valueType),
        );
    }

    /**
     * @param MetadataValueDTO[] $states
     */
    private function buildQueryByMetadataValue(array $states): Builder
    {
        $config = $this->getConfig();
        $query = $this->repository->getQuery();
        foreach ($states as $state) {
            $query->where($config->metadataKeyFieldName, $state->taskMetadataKey);
            if (is_array($state->taskMetadataValue)) {
                $query->whereIn($config->metadataValueFieldName, $state->taskMetadataValue);
            } else {
                $query->where($config->metadataValueFieldName, $state->taskMetadataValue);
            }
        }

        return $query;
    }

    private function buildRepository(): MetadataRepository
    {
        if (isset($this->repository)) {
            return $this->repository;
        }

        return new class (static::getEntity(), $this->getConfig()) extends MetadataRepository {
            public static string $entityClass;
            public static MetadataConfig $config;

            public function __construct(string $modelClass, MetadataConfig $config)
            {
                self::$entityClass = $modelClass;
                self::$config = $config;
                parent::__construct();
            }

            public static function getEntityInstance(): Metadata
            {
                return Metadata::build(self::$entityClass);
            }

            public static function getConfig(): MetadataConfig
            {
                return self::$config;
            }
        };
    }
}
