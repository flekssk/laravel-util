# FKS Facade

## Responsibility

A convenient way to get information about the context of a request.

The FKS context now includes the following information:
- `FKS::getCurrentDataOwnerId()`
- `FKS::getCurrentUserId()`
- `FKS::getCurrentDayId()`
- `FKS::getCreatingContext()`
- `FKS::getUpdatingContext()`
- `FKS::getSpecifiedContext(string $key)`

Important! `FKS::getCurrentDataOwnerId()` & `FKS::getCurrentUserId()` will not be defined in the console environment and will cause an error.

Additionally, you can get a specific `DayId` by date via `FKS::getSpecifiedDayId($specifiedDate)`.

## Motivation to use

The main reason for using the FKS facade is quick access to the state of the context and the ability to manage this state in a test environment, in contrast to the `DataOwner::getDataOwnerId()` static methods and the `getCurrentUserId` functions.

## Settings

To use this facade, you must implement the `FKS\Contracts\FKS` contract in your project.

<details>
  <summary>Example</summary>


```PHP
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataOwner;
use App\Models\Day;
use DateTimeInterface;
use FKS\Contracts\FKS;
use FKS\ValueObjects\Id;

class FKSService implements FKS
{
    public function getCurrentDataOwnerId(): Id
    {
        return Id::createFromBytes(DataOwner::getDataOwnerId());
    }

    public function getCurrentUserId(): Id
    {
        $uuid = getCurrentUserId();
        if (!$uuid) {
            throw new \RuntimeException('Undefined current user');
        }
        return Id::createFromString($uuid);
    }

    private ?Id $currentDate = null;

    public function getCurrentDayId(): Id
    {
        if (!$this->currentDate) {
            $this->currentDate = Id::createFromBytes(Day::getDayId());
        }
        return $this->currentDate;
    }

    private array $mapSpecifiedDatesToDayIds = [];

    public function getSpecifiedDayId(DateTimeInterface $specifiedDateTime): Id
    {
        $date = $specifiedDateTime->format('Y-m-d');
        $day = $this->mapSpecifiedDatesToDayIds[$date] ?? null;
        if (!$day) {
            $day = Id::createFromBytes(Day::getDayId($date));
            $this->mapSpecifiedDatesToDayIds[$date] = $day;
        }
        return $day;
    }
    
    public function getCreatingContext(): array
    {
        return [
            'created_at_day_id' => $this->getCurrentDayId(),
            'created_at' => Carbon::now('UTC'),
            'created_by' => $this->getCurrentUserId(),
        ];
    }

    public function getUpdatingContext(): array
    {
        return [
            'updated_at_day_id' => $this->getCurrentDayId(),
            'updated_at' => Carbon::now('UTC'),
            'updated_by' => $this->getCurrentUserId(),
        ];
    }
    
    public function getSpecifiedContext(string $name): array
    {
        return [
            "{$name}_at_day_id" => $this->getCurrentDayId(),
            "{$name}_at" => Carbon::now('UTC'),
            "{$name}_by" => $this->getCurrentUserId(),
        ];
    }
}
```

</details>

Next, the implementation of this contract needs to be mapped to the `FKS` alias in the application config `config/app.php`

```
'aliases' => [
    ...
    
    'FKS' => \App\Services\FKSService::class,
    
    ...
],
```

## Usage

```PHP
<?php

declare(strict_types=1);

namespace Modules\FKSNotifications\UseCases\V1\NotificationSubscription;

use Modules\FKSNotifications\Entities\NotificationSubscription;
use Modules\FKSNotifications\Repositories\NotificationSubscriptionsRepository;
use Illuminate\Support\Carbon;
use Modules\FKSNotifications\UseCases\V1\NotificationSubscription\DTO\NotificationSubscribeDTO;
use FKS\Exceptions\FKSDomainException;
use FKS\Facades\FKS;
use FKS\ValueObjects\SearchConditions\SearchConditions;
use Throwable;


class NotificationSubscriptionService
{
    public function __construct(private NotificationSubscriptionsRepository $repository) {}

    public function search(SearchConditions $searchConditions): \Traversable
    {
        return $this->repository->getList($searchConditions);
    }

    public function subscribe(NotificationSubscribeDTO $dto): NotificationSubscription
    {
        $activeSubscription = $this->repository->getActiveSubscription($dto->FKSNotificationTypeId);
        if ($activeSubscription) {
            $activeSubscription->update([
                'notification_time_from' => $dto->notificationTimeFrom,
                'notification_time_to' => $dto->notificationTimeTo,
                'only_workdays' => $dto->onlyWorkdays,
                'updated_at' => Carbon::now('UTC'),
                'updated_at_day_id' => FKS::getCurrentDayId(),
                'updated_by' => FKS::getCurrentUserId(),
            ]);
            return $activeSubscription;
        }

        return $this->repository->create(
            [
                'FKS_notification_type_id' => $dto->FKSNotificationTypeId,
                'notification_time_from' => $dto->notificationTimeFrom,
                'notification_time_to' => $dto->notificationTimeTo,
                'only_workdays' => $dto->onlyWorkdays,
                'user_id' => FKS::getCurrentUserId(),
                'data_owner_id' => FKS::getCurrentDataOwnerId(),
                'created_at' => Carbon::now('UTC'),
                'created_at_day_id' => FKS::getCurrentDayId(),
                'created_by' => FKS::getCurrentUserId(),
            ]
        );
    }

    public function unsubscribe(int $FKSNotificationTypeId): void
    {
        $activeSubscription = $this->repository->getActiveSubscription($FKSNotificationTypeId);
        if (!$activeSubscription) {
            throw new FKSDomainException('Subscription not found');
        }
        $activeSubscription->update([
            'deleted_at' => Carbon::now('UTC'),
            'deleted_at_day_id' => FKS::getCurrentDayId(),
            'deleted_by' => FKS::getCurrentUserId(),
        ]);
    }
}

```

### Alternative usage

```PHP
<?php

declare(strict_types=1);

namespace Modules\FKSNotifications\UseCases\V1\NotificationSubscription;

use Modules\FKSNotifications\Entities\NotificationSubscription;
use Modules\FKSNotifications\Repositories\NotificationSubscriptionsRepository;
use Illuminate\Support\Carbon;
use Modules\FKSNotifications\UseCases\V1\NotificationSubscription\DTO\NotificationSubscribeDTO;
use FKS\Exceptions\FKSDomainException;
use FKS\Facades\FKS;
use FKS\ValueObjects\SearchConditions\SearchConditions;
use Throwable;


class NotificationSubscriptionService
{
    public function __construct(private NotificationSubscriptionsRepository $repository) {}

    public function search(SearchConditions $searchConditions): \Traversable
    {
        return $this->repository->getList($searchConditions);
    }

    public function subscribe(NotificationSubscribeDTO $dto): NotificationSubscription
    {
        $activeSubscription = $this->repository->getActiveSubscription($dto->FKSNotificationTypeId);
        if ($activeSubscription) {
            $activeSubscription->update([
                'notification_time_from' => $dto->notificationTimeFrom,
                'notification_time_to' => $dto->notificationTimeTo,
                'only_workdays' => $dto->onlyWorkdays,
            ] + FKS::getUpdatingContext());
            return $activeSubscription;
        }

        return $this->repository->create(
            [
                'FKS_notification_type_id' => $dto->FKSNotificationTypeId,
                'notification_time_from' => $dto->notificationTimeFrom,
                'notification_time_to' => $dto->notificationTimeTo,
                'only_workdays' => $dto->onlyWorkdays,
                'user_id' => FKS::getCurrentUserId(),
                'data_owner_id' => FKS::getCurrentDataOwnerId(),
            ]  + FKS::getCreatingContext()
        );
    }

    public function unsubscribe(int $FKSNotificationTypeId): void
    {
        $activeSubscription = $this->repository->getActiveSubscription($FKSNotificationTypeId);
        if (!$activeSubscription) {
            throw new FKSDomainException('Subscription not found');
        }
        $activeSubscription->update(FKS::getSpecifiedContext('deleted'));
    }
}

```


