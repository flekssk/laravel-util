<?php

declare(strict_types=1);

namespace FKS\Services\Metadata;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use FKS\Helpers\Metadata\SxopeArrayDifferentiator;
use FKS\Services\Metadata\Collection\MetadataDiffCollection;
use FKS\Services\Metadata\DTO\MetadataAggregationResultDTO;
use FKS\Services\Metadata\DTO\MetadataCreateDTO;
use FKS\Services\Metadata\DTO\MetadataValueDTO;
use FKS\Services\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Services\Metadata\Enums\MetadataAggregationValueTypeEnum;
use FKS\Services\Metadata\Helpers\MetadataConfigHelper;
use FKS\Services\Metadata\Helpers\MetadataValueHelper;
use FKS\Services\Metadata\Models\Metadata;
use FKS\Services\Metadata\Repositories\MetadataRepository;
use FKS\ValueObjects\Id;

/**
 * @template ModelClass
 */
abstract class MetadataService
{
    use SxopeArrayDifferentiator;

    public MetadataRepository $repository;
    public MetadataConfig $config;

    abstract public static function getEntity(): string;

    public function __construct()
    {
        $this->config = MetadataConfigHelper::getModelConfig(static::getEntity());
        $this->repository = $this->buildRepository();
    }

    public function getConfig(): MetadataConfig
    {
        return $this->config;
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

    public function upsertMetadataChunk(Id $entityId, array $metadataValues, Id $userId): MetadataDiffCollection
    {
        $diff = [];
        $existedMetadata = $this->repository
            ->getQuery()
            ->where($this->config->entityPrimaryKey, $entityId)
            ->whereIn(
                $this->config->metadataKeyFieldName,
                collect($metadataValues)
                    ->map(
                        function ($metadataValue, $metadataKey) {
                            if ($metadataValue instanceof MetadataValueDTO) {
                                $metadataKey = $metadataValue->metadataKey;
                            }
                            return $metadataKey;
                        }
                    )
            )
            ->get();

        $metadataValueFieldName = $this->config->metadataValueFieldName;
        $metadataKeyFieldName = $this->config->metadataKeyFieldName;
        foreach ($metadataValues as $metadataKey => $metadataValue) {
            if ($metadataValue instanceof MetadataValueDTO) {
                $metadataKey = $metadataValue->metadataKey;
                $metadataValue = $metadataValue->metadataValue;
            }
            $metadata = $existedMetadata->where($metadataKeyFieldName, $metadataKey)->first();
            if ($metadata === null) {
                $diff[$metadataKey] = [
                    'old_value' => '',
                    'new_value' => MetadataValueHelper::toString($metadataValue),
                ];
                $this->create(
                    $entityId,
                    new MetadataValueDTO(
                        $metadataKey,
                        $metadataValue,
                    ),
                    $userId,
                );
            } elseif ($this->areDifferent(
                MetadataValueHelper::toString($metadata->$metadataValueFieldName),
                MetadataValueHelper::toString(
                    MetadataValueHelper::applyMutators($this->getConfig(), $metadataKey, $metadataValue))
            )
            ) {
                $diff[$metadataKey] = [
                    'old_value' => MetadataValueHelper::toString($metadata->$metadataValueFieldName),
                    'new_value' => MetadataValueHelper::toString(
                        MetadataValueHelper::applyMutators($this->getConfig(), $metadataKey, $metadataValue)
                    ),
                ];
                $this->update(
                    Id::create($metadata->{$this->config->primaryKey}),
                    $entityId,
                    new MetadataValueDTO(
                        $metadataKey,
                        $metadataValue,
                    ),
                    $userId,
                );
            }
        }

        return new MetadataDiffCollection($diff);
    }

    public function create(Id $entityId, MetadataValueDTO $metadataValue, Id $userId): void
    {
        $this->repository->create(
            [
                $this->config->entityPrimaryKey => $entityId,
                $this->config->metadataKeyFieldName => $metadataValue->metadataKey,
                $this->config->metadataValueFieldName => MetadataValueHelper::toString($metadataValue->metadataValue),
                'created_at_day_id' => Sxope::getCurrentDayId(),
                'created_at' => Carbon::now('UTC')->format('c'),
                'created_by' => $userId,
            ]
        );
    }

    public function update(Id $metadataId, Id $entityId, MetadataValueDTO $metadataValue, Id $userId): void
    {
        $this->repository->updateByWhere(
            [
                $this->config->primaryKey => $metadataId,
                $this->config->entityPrimaryKey => $entityId,
                $this->config->metadataKeyFieldName => $metadataValue->metadataKey,
            ],
            [
                $this->config->metadataValueFieldName => MetadataValueHelper::toString($metadataValue->metadataValue),
                'updated_at_day_id' => Sxope::getCurrentDayId(),
                'updated_at' => Carbon::now('UTC')->format('c'),
                'updated_by' => $userId,
            ]
        );
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
                'created_at_day_id' => Sxope::getCurrentDayId(),
                'created_at' => Carbon::now('UTC')->format('c'),
                'created_by' => $metadataCreateDTO->userId,
            ],
            [
                'updated_at_day_id' => Sxope::getCurrentDayId(),
                'updated_at' => Carbon::now('UTC')->format('c'),
                'updated_by' => $metadataCreateDTO->userId,
            ]
        );
    }

    public function aggregate(
        Builder $builder,
        string $metadataKey,
        MetadataAggregationTypeEnum $aggregation,
        MetadataAggregationValueTypeEnum $valueType,
        SpannerJoinMethodsEnum $joinMethod = null
    ): MetadataAggregationResultDTO {
        return new MetadataAggregationResultDTO(
            $metadataKey,
            $aggregation,
            $valueType,
            $this->repository->aggregate($builder, $metadataKey, $aggregation, $valueType, $joinMethod),
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

    public function buildRepository(bool $recreate = false, bool $onlyMetadataKeys = false): MetadataRepository
    {
        $this->config->onlyMetadataKeys = $onlyMetadataKeys;

        return new class (static::getEntity(), $this->config) extends MetadataRepository {
            public static string $entityClass;
            public MetadataConfig $config;

            public function __construct(string $modelClass, MetadataConfig $config)
            {
                self::$entityClass = $modelClass;
                $this->config = $config;
                parent::__construct();
            }

            public function getConfig(): MetadataConfig
            {
                return $this->config;
            }

            public static function getEntityInstance(): Metadata
            {
                return Metadata::build(MetadataConfigHelper::getModelConfig(self::$entityClass));
            }
        };
    }
}
