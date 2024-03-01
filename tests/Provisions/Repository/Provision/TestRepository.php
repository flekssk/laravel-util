<?php

declare(strict_types=1);

namespace Tests\Provisions\Repository\Provision;

use Illuminate\Database\Eloquent\Model;
use FKS\Repositories\Repository;
use FKS\Repositories\SearchRepository;

/**
 * @extends Repository<Model>
 */
class TestRepository extends SearchRepository
{
    public static function getEntityInstance(): Model
    {
        return new TestModel();
    }
}