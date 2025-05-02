<?php

declare(strict_types=1);

namespace FKS\ActivityLog\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

abstract class ActivityPayload implements Arrayable
{
    abstract public function getActivityTypeId(): int;

    public function toArray(): array
    {
        return [];
    }
}
