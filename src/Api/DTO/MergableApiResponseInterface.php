<?php

declare(strict_types=1);

namespace FKS\Api\DTO;

interface MergableApiResponseInterface
{
    public function mergeData(self $response): self;
    public function count(): int;
}
