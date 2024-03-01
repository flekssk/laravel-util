<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ActivityLog\Formatters;

use Colopl\Spanner\Eloquent\Model;
use FKS\Services\ActivityLog\Formatters\Formatter;

class TestFormatter extends Formatter
{
    public function handle(Model $item): Model
    {
        $item->payload = ['test' => 'test'];

        return $item;
    }
}
