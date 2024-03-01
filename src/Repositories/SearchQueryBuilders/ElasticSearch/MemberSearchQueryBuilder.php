<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\ElasticSearch;

use FKS\Contracts\DataApiClientInterface;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\ElasticSearch\ElasticSearchQueryBuilder;
use FKS\ValueObjects\Id;

class MemberSearchQueryBuilder implements BuilderInterface
{
    private DataApiClientInterface $dataApiClient;

    public function __construct(DataApiClientInterface $dataApi)
    {
        $this->dataApiClient = $dataApi;
    }

    /**
     * @param ElasticSearchQueryBuilder $builder
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $foundMemberIds = $this->dataApiClient->getSearchMembersIds($condition->getMemberSearch(), $condition->getContainsPcpIds());
        if (count($foundMemberIds)) {
            $builder->whereIn($column, Id::mapArrayValueToUuid($foundMemberIds));
        } else {
            $builder->whereIn($column, ['FALSE']);
        }
    }
}
