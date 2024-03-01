<?php

declare(strict_types=1);

namespace FKS\Services\ActivityLog\Formatters;

use Colopl\Spanner\Eloquent\Model;

abstract class Formatter
{
    public function handle(Model $item): Model
    {
        return $item;
    }
}
