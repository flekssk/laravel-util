<?php

declare(strict_types=1);

namespace FKS\Metadata;

use Carbon\Carbon;
use FKS\Metadata\Helpers\ArrayDifferentiator;
use FKS\Metadata\Models\Metadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use FKS\Metadata\Collection\MetadataDiffCollection;
use FKS\Metadata\DTO\MetadataAggregationResultDTO;
use FKS\Metadata\DTO\MetadataCreateDTO;
use FKS\Metadata\DTO\MetadataValueDTO;
use FKS\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Metadata\Enums\MetadataAggregationValueTypeEnum;
use FKS\Metadata\Helpers\MetadataConfigHelper;
use FKS\Metadata\Helpers\MetadataValueHelper;
use FKS\Metadata\Repositories\MetadataRepository;

/**
 * @template ModelClass
 */
abstract class MetadataService
{
    use ArrayDifferentiator;

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

    public function upsertMetadataChunk(int $entityId, array $metadataValues, int $userId): MetadataDiffCollection
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
                    $metadata->{$this->config->primaryKey},
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

    public function create(int $entityId, MetadataValueDTO $metadataValue, int $userId): void
    {
        $this->repository->create(
            [
                $this->config->entityPrimaryKey => $entityId,
                $this->config->metadataKeyFieldName => $metadataValue->metadataKey,
                $this->config->metadataValueFieldName => MetadataValueHelper::toString($metadataValue->metadataValue),
                'created_at' => Carbon::now('UTC')->format('c'),
            ]
        );
    }

    public function update(int $metadataId, int $entityId, MetadataValueDTO $metadataValue, int $userId): void
    {
        $this->repository->updateByWhere(
            [
                $this->config->primaryKey => $metadataId,
                $this->config->entityPrimaryKey => $entityId,
                $this->config->metadataKeyFieldName => $metadataValue->metadataKey,
            ],
            [
                $this->config->metadataValueFieldName => MetadataValueHelper::toString($metadataValue->metadataValue),
                'updated_at' => Carbon::now('UTC'),
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
                'created_at' => Carbon::now('UTC')->format('c'),
            ],
            [
                'updated_at' => Carbon::now('UTC')->format('c'),
            ]
        );
    }

    public function aggregate(
        Builder $builder,
        string $metadataKey,
        MetadataAggregationTypeEnum $aggregation,
        MetadataAggregationValueTypeEnum $valueType,
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
            $query->where($config->metadataKeyFieldName, $state->metadataKey);
            if (is_array($state->metadataValue)) {
                $query->whereIn($config->metadataValueFieldName, $state->metadataValue);
            } else {
                $query->where($config->metadataValueFieldName, $state->metadataValue);
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
