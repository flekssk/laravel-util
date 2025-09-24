<?php

declare(strict_types=1);

namespace FKS\Api\DTO;

use FKS\Serializer\SerializableObject;

class ApiResponse extends SerializableObject implements \Countable
{
    public mixed $data;

    public function __construct(mixed $data = [])
    {
        $this->data = $data;
    }

    public function mergeData(self $response): self
    {
        $this->data = array_merge($this->data, $response->data);

        return $this;
    }

    public function count(): int
    {
        return count($this->data);
    }
}
