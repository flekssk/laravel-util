<?php

declare(strict_types=1);

namespace FKS\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionException;
use FKS\Search\Collections\EntitiesCollection;
use FKS\Search\Contracts\RepositoryInterface;
use FKS\Search\Repositories\Exceptions\RepositoryException;
use FKS\Search\ValueObjects\WhereCondition;

/**
 * Class Repository
 *
 * @template ModelClass
 */
abstract class Repository implements RepositoryInterface
{
    public const DEFAULT_CHUNK_LENGTH = 200;

    /**
     * @var ModelClass|Model
     */
    public Model $entityInstance;
    protected bool $isSoftDeleteExpected;
    protected bool $hasSoftDeletedTrait;

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->initializeRepository();
    }

    /**
     * @return $this
     */
    public function getRepositoryWithoutSoftDelete(): self
    {
        $repository = clone ($this);
        $repository->isSoftDeleteExpected = false;

        return $repository;
    }

    /**
     * @param array $columns
     * @return EntitiesCollection<ModelClass>
     */
    public function getAll(array $columns = ['*']): Collection
    {
        $query = $this->getQuery();

        return $query->get($columns);
    }

    /**
     * @param array|WhereCondition $where
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $forceIndex
     * @return EntitiesCollection<ModelClass>
     */
    public function getByWhere($where, ?int $limit = null, ?int $offset = null, ?string $forceIndex = null): Collection
    {
        return $this->getQueryByWhere($where, $limit, $offset, $forceIndex)->get();
    }

    /**
     * @param $id
     * @param array $columns
     * @return ModelClass
     */
    public function findById($id, array $columns = ['*']): ?Model
    {
        $query = $this->getQuery()
            ->select($columns)
            ->where($this->entityInstance->getKeyName(), $id);

        return $query
            ->limit(1)
            ->get()
            ->first();
    }

    /**
     * @param array|WhereCondition $where
     * @return ModelClass|null
     */
    public function findByWhere(array|WhereCondition $where): ?Model
    {
        return $this->getByWhere($where)->first();
    }

    /**
     * @param array|WhereCondition $where
     * @return bool
     */
    public function exists($where): bool
    {
        $this->validateWhereStatement($where);

        $query = $this->getQuery();
        $this->applyWhere($where, $query);

        return $query->exists();
    }

    public function updateByWhereByChunk(
        array|WhereCondition $where,
        array $values,
        bool $returnUpdated = false,
        int $chunkLength = self::DEFAULT_CHUNK_LENGTH,
        ?string $forceIndex = null
    ): int|Collection {
        $updatedCount = 0;
        $updatedEntities = collect();
        $query = $this->getQuery();
        $this->applyWhere($where, $query);

        if (!is_null($forceIndex)) {
            $query->forceIndex($forceIndex);
        }

        $query->chunkById($chunkLength, function ($data) use ($values, &$updatedCount, &$updatedEntities, $returnUpdated) {
            $primaryKey = $data->first()->getKeyName();
            $ids = array_map(static fn($item) => $item->{$primaryKey}, $data->all());
            $getQuery = $this->getQuery()->whereIn($primaryKey, $ids);
            $updatedQuery = (clone $getQuery);
            $updatedCount += $getQuery->update($values);
            if ($returnUpdated) {
                $updatedEntities = $updatedEntities->merge($updatedQuery->get());
            }
        });

        return $returnUpdated ? $updatedEntities : $updatedCount;
    }

    /**
     * @param $id
     * @param array $values
     * @param bool $returnUpdated
     * @return bool|ModelClass
     */
    public function updateById(
        $id,
        array $values,
        bool $returnUpdated = false,
    ): bool|Model {
        $result = $this->updateByWhere(
            [
                $this->entityInstance->getKeyName() => $id
            ],
            $values,
            $returnUpdated,
        );

        return $returnUpdated ? $result->first(default: false) : $result > 0;
    }

    /**
     * @param array|WhereCondition $where
     * @param array $values
     * @param bool $returnUpdated
     * @return int|EntitiesCollection<ModelClass>
     */
    public function updateByWhere(
        array|WhereCondition $where,
        array $values,
        bool $returnUpdated = false,
    ): int|Collection {

        if ($returnUpdated) {
            return $this->updateByWhereByChunk(
                $where,
                $values,
                $returnUpdated,
                800
            );
        }

        $query = $this->getQuery();
        $this->applyWhere($where, $query);

        return $query->update($values);
    }

    /**
     * @param array|WhereCondition $where
     * @param bool $returnDeleted
     * @param int $chunkSize
     * @return int|EntitiesCollection<ModelClass>
     */
    public function deleteByChunk(
        array|WhereCondition $where,
        bool $returnDeleted = false,
        int $chunkSize = self::DEFAULT_CHUNK_LENGTH
    ): Collection|int {
        $deletedCount = 0;
        $deletedEntities = collect();

        $this->validateWhereStatement($where);

        $query = $this->getQuery();
        $this->applyWhere($where, $query);

        $query
            ->chunkById($chunkSize, function ($data) use (&$deletedCount, &$deletedEntities, $returnDeleted) {
                $primaryKey = $data->first()->getKeyName();
                $ids = array_map(static fn($item) => $item->{$primaryKey}, $data->all());
                $getQuery = $this->getQuery()->whereIn($primaryKey, $ids);
                if ($returnDeleted) {
                    $deleteQuery = (clone $getQuery);
                    $deletedEntities = $deletedEntities->merge($deleteQuery->get());
                }
                if ($this->isSoftDeleteExpected) {
                    $deletedCount += $getQuery->update(
                        [
                            'deleted_at' => Carbon::now('UTC'),
                        ]
                    );
                } else {
                    $deletedCount += $getQuery->forceDelete();
                }
            });

        return $returnDeleted ? $deletedEntities : $deletedCount;
    }

    /**
     * @param array|WhereCondition $where
     * @param bool $returnDeleted
     * @param int|null $limit
     * @return int|EntitiesCollection<ModelClass>
     */
    public function deleteByWhere(
        array|WhereCondition $where,
        bool $returnDeleted = false,
        ?int $limit = null
    ): int|Collection {
        $this->validateWhereStatement($where);

        $query = $this->getQuery();
        $this->applyWhere($where, $query);

        if ($limit) {
            $query->limit($limit);
        }

        if ($returnDeleted) {
            $wasDeleted = (clone $query)->get();
        }
        if ($this->isSoftDeleteExpected) {
            $deletedCount = $query->update(
                [
                    'deleted_at' => Carbon::now('UTC'),
                ]
            );
        } else {
            $deletedCount = $query->forceDelete();
        }

        return $returnDeleted ? $wasDeleted : $deletedCount;
    }

    /**
     * @param array $values
     * @return ModelClass
     */
    public function create(array $values): Model
    {
        return $this->getQuery()->create($values);
    }

    /**
     * @return ModelClass
     */
    public function updateOrCreate(
        array $attributes = [],
        array $values = [],
        array $createValues = [],
        array $updateValues = []
    ): Model {
        /** @var Model|null $instance */
        $instance = $this->getByWhere($attributes)->first();

        if ($instance === null) {
            $instance = $this->getQuery()->getModel()->fill(array_merge($attributes, $values, $createValues));
            $instance->save();

            return $instance;
        } else {
            $updateValues = array_merge($values, $updateValues);

            $shouldBeUpdated = false;
            foreach ($updateValues as $columnName => $updateValue) {
                if (
                    !in_array($columnName, ['updated_at', 'updated_by', 'updated_day_id'])
                    && $instance->$columnName !== $updateValue
                ) {
                    $shouldBeUpdated = true;
                }
            }
            if ($shouldBeUpdated === true) {
                $instance->fill(array_merge($values, $updateValues))
                    ->save();
            }
            $instance->existed = true;
        }

        return $instance;
    }

    /**
     * @param array $values
     * @param int $chunkSize
     * @return void
     */
    public function createByChunk(array $values, int $chunkSize = self::DEFAULT_CHUNK_LENGTH): void
    {
        DB::transaction(function () use ($values, $chunkSize) {
            foreach (array_chunk($values, $chunkSize) as $chunkValues) {
                $this->createBatch($chunkValues);
            }
        });
    }

    /**
     * @param array $values
     * @return bool
     */
    public function insert(array $values): bool
    {
        return $this->getQuery()->insert($values);
    }

    public function insertOrIgnore(array $values): int
    {
        return $this->getQuery()->insertOrIgnore($values);
    }

    public function getQuery(): Builder
    {
        return $this->isSoftDeleteExpected
            ? $this->entityInstance::query()
            : ($this->hasSoftDeletedTrait ? $this->entityInstance::withTrashed() : $this->entityInstance::query());
    }

    public function getNewModelQuery(): Builder
    {
        return $this->entityInstance->newModelQuery();
    }

    public function createBatch(array $items): bool
    {
        return $this->getNewModelQuery()->insert($items);
    }

    /**
     * @return EntitiesCollection<ModelClass>
     */
    public function getByQuery(Builder|\Illuminate\Database\Query\Builder $query): Collection
    {

        /** @var Model $instance */
        $instance = $this->entityInstance;

        if ($this->isSoftDeleteExpected) {
            $query->whereNull( $instance->getTable() . '.deleted_at');
        }

        return $query->get();
    }

    /**
     * @param $where
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $forceIndex
     * @return Builder
     */
    protected function getQueryByWhere($where, ?int $limit = null, ?int $offset = null, ?string $forceIndex = null): Builder
    {
        $this->validateWhereStatement($where);

        $query = $this->getQuery();
        $query->select('*');

        $this->applyWhere($where, $query);

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        if (!is_null($offset)) {
            $query->offset($offset);
        }

        if (!is_null($forceIndex)) {
            $query->forceIndex($forceIndex);
        }

        return $query;
    }

    /**
     * @param mixed $where
     * @return void
     */
    private function validateWhereStatement(mixed $where): void
    {
        if (!$where instanceof WhereCondition && !is_array($where)) {
            throw RepositoryException::invalidWhereExpression(get_debug_type($where));
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function initializeRepository(): void
    {
        $this->entityInstance = static::getEntityInstance();

        $entityReflection = new ReflectionClass(static::getEntityInstance());
        $this->isSoftDeleteExpected = array_key_exists(SoftDeletes::class, $entityReflection->getTraits());
        $this->hasSoftDeletedTrait = array_key_exists(SoftDeletes::class, $entityReflection->getTraits());
    }

    /**
     * @return ModelClass
     * @psalm-return ModelClass
     */
    abstract public static function getEntityInstance(): Model;

    public function applyWhere(array|WhereCondition $where, Builder $query): void
    {
        if ($where instanceof WhereCondition) {
            foreach ($where->getRawWhere() as $item) {
                $query->whereRaw($item);
            }
            foreach ($where->getWhereIn() as $column => $columnValues) {
                $query->whereIn($column, $columnValues);
            }
            foreach ($where->getWhereNotIn() as $column => $columnValues) {
                $query->whereNotIn($column, $columnValues);
            }
            if ($where->getClosure() !== null) {
                $query->where($where->getClosure());
            }
            $query->where($where->getWhere());
        } else {
            $query->where($where);
        }
    }

    /**
     * @return ModelClass
     */
    public function replicate(Model $order, array $attributes = []): Model
    {
        return tap($order->replicate()->fill($attributes))->save();
    }

    /**
     * @return EloquentCollection<ModelClass>
     */
    public function findMany(array $ids): EloquentCollection
    {
        return $this->getQuery()->findMany($ids);
    }
}
