<?php

declare(strict_types=1);

namespace FKS\Api\DTO;

use FKS\Services\Serializer\SerializableObject;

class ApiResponse extends SerializableObject
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
}
