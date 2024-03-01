<?php

declare(strict_types=1);

namespace FKS\Repositories;

class ColumnParamRaw
{
    public function __construct(
        public string $raw,
        public ?string $tableName = null,
    ) {
    }
}
