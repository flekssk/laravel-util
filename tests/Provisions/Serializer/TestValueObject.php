<?php

declare(strict_types=1);

namespace Tests\Provisions\Serializer;

use FKS\Services\Serializer\DTO\SerializableDTO;

class TestValueObject extends SerializableDTO
{
    public function __construct(public string $value = 'test')
    {
    }
}