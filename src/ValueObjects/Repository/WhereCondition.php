<?php

namespace FKS\ValueObjects\Repository;

class WhereCondition
{
    public function __construct(
        private readonly array $where = [],
        private readonly array $whereRaw = [],
        private readonly array $whereIn = [],
        private readonly ?\Closure $closure = null,
        private readonly array $whereNotIn = [],
    )
    {
    }

    public function getWhere(): array
    {
        return $this->where;
    }

    public function getRawWhere(): array
    {
        return $this->whereRaw;
    }

    public function getWhereIn(): array
    {
        return $this->whereIn;
    }

    public function getClosure(): ?\Closure
    {
        return $this->closure;
    }

    public function getWhereNotIn(): array
    {
        return $this->whereNotIn;
    }
}
