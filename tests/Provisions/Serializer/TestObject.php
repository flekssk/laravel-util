<?php

declare(strict_types=1);

namespace Tests\Provisions\Serializer;

use FKS\Services\Serializer\DTO\SerializableDTO;
use FKS\ValueObjects\Id;

class TestObject extends SerializableDTO
{
    /**
     * @param TestValueObject[]|null $valueObjectsValue
     */
    public function __construct(
        public Id $id,
        public string $stringValue,
        public int $intValue,
        public bool $booleanValue,
        public TestValueObject $valueObjectValue,
        public ?array $valueObjectsValue,
        public ?string $nullableValue,
    ) {
    }
}