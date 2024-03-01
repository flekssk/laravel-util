<?php

declare(strict_types=1);

namespace FKS\Repositories;

class ColumnParamMap
{
    public function __construct(
        public string $tableName,
        public string $tableColumn,
        public ?string $tableValuesColumn = null,
    ) {
    }
}
