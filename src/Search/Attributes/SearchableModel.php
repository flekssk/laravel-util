<?php

declare(strict_types=1);

namespace FKS\Search\Attributes;

use OpenApi\Attributes\Schema;
use FKS\Search\Repositories\SearchRepository;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SearchableModel
{
    /**
     * @var class-string<SearchRepository>
     */
    public string $repository;
    /**
     * @var class-string<Schema>|null
     */
    public ?string $swaggerSchema;

    public function __construct(string $repository, ?string $swaggerSchema = null)
    {
        $this->repository = $repository;
        $this->swaggerSchema = $swaggerSchema;
    }
}
