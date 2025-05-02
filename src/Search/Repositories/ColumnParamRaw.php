<?php

declare(strict_types=1);

namespace FKS\Search\Repositories;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class ColumnParamRaw extends AbstractColumnParam
{
    public function __construct(
        public string $raw,
        public ?string $tableName = null,
        public ?string $aggregateBy = null,
    ) {
    }

    function getSortColumn($sortDefinition): string|Expression
    {
        return DB::raw($this->raw);
    }
}
