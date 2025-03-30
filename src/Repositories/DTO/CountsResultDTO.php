<?php

declare(strict_types=1);

namespace FKS\Repositories\DTO;

use FKS\Services\Serializer\SerializableObject;

class CountsResultDTO extends SerializableObject
{
    public function __construct(public int $totalCounts, public array $list)
    {
    }
}