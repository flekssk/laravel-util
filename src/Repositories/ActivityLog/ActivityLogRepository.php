<?php

declare(strict_types=1);

namespace FKS\Repositories\ActivityLog;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use FKS\Repositories\SearchRepository;
use FKS\ValueObjects\SearchConditions\SearchConditions;

class ActivityLogRepository extends SearchRepository
{
    public function getList(SearchConditions $searchConditions): Collection|array
    {
        $query = $this->getNewModelQuery()
            ->select([
                'activity_logs.*',
                'activity_types.activity_type_name'
            ]);

        $this->applyAllConditions($query, $searchConditions);

        return $query->get();
    }

    protected function getJoinsDefinitions(): array
    {
        return [
            function ($builder) {
                $builder->join('activity_types', 'activity_types.activity_type_id', 'activity_logs.activity_type_id');
            },
        ];
    }

    public static function getMapFilterParamToColumn(): array
    {
        return [
            'activity_type_id' => 'activity_logs.activity_type_id'
        ];
    }

    public static function getEntityInstance(): Model
    {
        $class = config('activity-log.activity-log-model-class');

        if (!is_a($class, Model::class, true)) {
            throw new \DomainException('activity-log.activity-log-model-class must be Model class string');
        }

        return new $class();
    }
}
