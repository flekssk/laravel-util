<?php

declare(strict_types=1);

namespace FKS\Services\ActivityLog;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use FKS\Casts\JsonObjectCast;
use FKS\Exceptions\FKSDomainException;
use FKS\Repositories\ActivityLog\ActivityLogRepository;
use FKS\Services\ActivityLog\Formatters\Formatter;
use FKS\Services\ActivityLog\ValueObjects\ActivityPayload;
use GuzzleHttp\Utils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use FKS\Facades\FKS;
use FKS\ValueObjects\Id;
use Webmozart\Assert\Assert;

class ActivityLogService
{
    public function __construct(protected readonly ActivityLogRepository $repository)
    {
        if (!config('FKS-activity-log.enable', false)) {
            throw new FKSDomainException(
                'To use activity log FKS-activity-log.enable config params must be true'
            );
        }
    }

    public function capture($entityId, int $entityTypeId, ActivityPayload $payload, $createdBy = null): Model
    {
        $data = [
            'entity_id' => Id::create($entityId),
            'entity_type_id' => $entityTypeId,
            'data_owner_id' => FKS::getCurrentDataOwnerId(),
            'activity_type_id' => $payload->getActivityTypeId(),
            'payload' => $this->repository->entityInstance->hasCast('payload', JsonObjectCast::class)
                ? $payload->toArray()
                : DB::raw(
                    sprintf(
                        "JSON '%s'",
                        json_encode($payload->toArray(), JSON_THROW_ON_ERROR | JSON_HEX_APOS)
                    )
                ),
        ];
        if (!is_null($createdBy)) {
            $data += [
                'created_by' => Id::create($createdBy),
                'created_at' => Carbon::now(),
                'created_at_day_id' => FKS::getCurrentDayId(),
            ];
        } else {
            $data += FKS::getCreatingContext();
        }
        return $this->repository->create($data);
    }

    public function listPreparation($data): Collection
    {
        $newData = collect();
        foreach ($data as $item) {
            if (!$this->repository->entityInstance->hasCast('payload', JsonObjectCast::class)) {
                $item->payload = is_array($item->payload) ? $item->payload : Utils::jsonDecode($item->payload, true);
            }
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
            $values = [
                'activity_log_id' => FKS::getNewId(),
                'entity_id' => Id::create($id),
                'data_owner_id' => FKS::getCurrentDataOwnerId(),
                'entity_type_id' => $entityTypeId,
                'activity_type_id' => $activityTypeId,
                'payload' => DB::raw("JSON '[]'"),
            ];
            if ($createdBy !== null) {
                $values['created_by'] = Id::create($createdBy);
            }
            $data[] = $values + FKS::getCreatingContext();
        }
        $this->repository->createByChunk($data, 100);
    }

    public static function entityTypes(): array
    {
        $typeIds = config('FKS-activity-log.entity-types');

        if (is_a($typeIds, \BackedEnum::class, true)) {
            $values = [];
            foreach ($typeIds::cases() as $typeId) {
                $values[$typeId->name] = $typeId->value;
            }
            $typeIds = $values;
        }

        if (!is_array($typeIds)) {
            throw new FKSDomainException('FKS-activity-log.entity-type-ids parameter must be an array');
        }

        return $typeIds;
    }

    public static function activityTypes(): array
    {
        $typeIds = config('FKS-activity-log.activity-types');

        if (is_a($typeIds, \BackedEnum::class, true)) {
            $values = [];
            foreach ($typeIds::cases() as $typeId) {
                $values[$typeId->name] = $typeId->value;
            }
            $typeIds = $values;
        }

        if (!is_array($typeIds)) {
            throw new FKSDomainException('FKS-activity-log.activity-types parameter must be an array');
        }

        return $typeIds;
    }

    public static function activityTypeFormatters(): array
    {
        $typeIds = config('FKS-activity-log.activity-type-formatters');

        if (!is_array($typeIds)) {
            throw new FKSDomainException('FKS-activity-log.activity-type-formatters parameter must be an array');
        }

        return $typeIds;
    }
}
