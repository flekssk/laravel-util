<?php

declare(strict_types=1);

namespace FKS\Search\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function create(array $values): Model;
    public function findById($id, array $columns = ['*']): ?Model;
    public function getByWhere(array $where, ?int $limit = null, ?int $offset = null): Collection;
    public function updateByWhere(array $where, array $values, bool $returnUpdated = false);
    public function exists(array $where): bool;
}
