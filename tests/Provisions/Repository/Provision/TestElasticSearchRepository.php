<?php

declare(strict_types=1);

namespace Tests\Provisions\Repository\Provision;

use Illuminate\Database\Eloquent\Model;
use FKS\Repositories\ElasticSearchRepository;
use FKS\Repositories\Repository;
use FKS\Repositories\SearchRepository;
use FKS\ValueObjects\SearchConditions\SearchConditions;

/**
 * @extends Repository<Model>
 */
class TestElasticSearchRepository extends ElasticSearchRepository
{
    protected function getIndexName(): string
    {
        return 'test_index';
    }

    public function search(SearchConditions $searchConditions)
    {
        $this->applyAllConditions($this->builder, $searchConditions);
    }
}