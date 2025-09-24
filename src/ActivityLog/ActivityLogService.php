<?php

declare(strict_types=1);

namespace FKS\ActivityLog;

use BackedEnum;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use FKS\ActivityLog\Formatters\Formatter;
use FKS\ActivityLog\Repositories\ActivityLog\ActivityLogRepository;
use FKS\ActivityLog\ValueObjects\ActivityPayload;
use Webmozart\Assert\Assert;

class ActivityLogService
{
    public function __construct(protected readonly ActivityLogRepository $repository)
    {
        if (!config('activity-log.enable', false)) {
            throw new DomainException(
                'To use activity log activity-log.enable config params must be true'
            );
        }
    }

    public function capture($entityId, int $entityTypeId, ActivityPayload $payload): Model
    {
        $data = [
            'entity_id' => $entityId,
            'entity_type_id' => $entityTypeId,
            'activity_type_id' => $payload->getActivityTypeId(),
            'payload' => $payload,
            'created_at' => Carbon::now(),
        ];

        return $this->repository->create($data);
    }

    public function listPreparation($data): Collection
    {
        $newData = collect();
        foreach ($data as $item) {
            $formatterClass = self::activityTypeFormatters()[$item['activity_type_id']] ?? null;
            if ($formatterClass === null) {
                $newData->add($item);
                continue;
            }
            /** @var Formatter $formatter */
            $formatter = app($formatterClass);
            $newData->add($formatter->handle($item));
        }
        return $newData;
    }

    public function generateActivities(int $entityTypeId, int $activityTypeId, array $ids = [], $createdBy = null): void
    {
        Assert::oneOf($activityTypeId, self::activityTypes());
        Assert::oneOf($entityTypeId, self::entityTypes());
        $data = [];
        foreach ($ids as $id) {
            $data[] = [
                'entity_id' => $id,
                'entity_type_id' => $entityTypeId,
                'activity_type_id' => $activityTypeId,
                'payload' => [],
                'created_at' => Carbon::now(),
            ];
        }
        $this->repository->createByChunk($data, 100);
    }

    public static function entityTypes(): array
    {
        $typeIds = config('activity-log.entity-types');

        if (is_a($typeIds, BackedEnum::class, true)) {
            $values = [];
            foreach ($typeIds::cases() as $typeId) {
                $values[$typeId->name] = $typeId->value;
            }
            $typeIds = $values;
        }

        if (!is_array($typeIds)) {
            throw new DomainException('activity-log.entity-type-ids parameter must be an array');
        }

        return $typeIds;
    }

    public static function activityTypes(): array
    {
        $typeIds = config('activity-log.activity-types');

        if (is_a($typeIds, BackedEnum::class, true)) {
            $values = [];
            foreach ($typeIds::cases() as $typeId) {
                $values[$typeId->name] = $typeId->value;
            }
            $typeIds = $values;
        }

        if (!is_array($typeIds)) {
            throw new DomainException('activity-log.activity-types parameter must be an array');
        }

        return $typeIds;
    }

    public static function activityTypeFormatters(): array
    {
        $typeIds = config('activity-log.activity-type-formatters');

        if (!is_array($typeIds)) {
            throw new DomainException('activity-log.activity-type-formatters parameter must be an array');
        }

        return $typeIds;
    }
}
