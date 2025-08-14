<?php

declare(strict_types=1);

namespace FKS\Search\Repositories;

use Illuminate\Contracts\Database\Query\Expression;

class ColumnParamString extends AbstractColumnParam
{
    public function __construct(public string|Expression $sortColumn) {
    }

    public function getSortColumn($sortDefinition): string|Expression
    {
        return $this->sortColumn;
    }
}
