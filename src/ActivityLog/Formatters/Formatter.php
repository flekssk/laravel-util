<?php

declare(strict_types=1);

namespace FKS\ActivityLog\Formatters;

use Illuminate\Database\Eloquent\Model;

abstract class Formatter
{
    public function handle(Model $item): Model
    {
        return $item;
    }
}
