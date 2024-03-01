<?php

declare(strict_types=1);

namespace Tests\Provisions\Serializer;

use FKS\Services\Serializer\DTO\SerializableDTO;

class TestValueObjectCollection extends SerializableDTO
{
    /**
     * @param TestObject[] $items
     * @param TestObject[] $itemsWithoutType
     */
    public function __construct(
        public ?array $items,
        public mixed $itemsWithoutType
    ) {
    }
}