<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use FKS\Contracts\DataApiClientInterface;
use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\ValueObjects\Id;

class MemberSearchQueryBuilder implements BuilderInterface
{
    private DataApiClientInterface $dataApiClient;

    public function __construct(DataApiClientInterface $dataApi)
    {
        $this->dataApiClient = $dataApi;
    }

    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        $isRawQueryExpected = SearchComponentConfigHelper::isUseRawQueryStatements();

        $foundMemberIds = $this->dataApiClient->getSearchMembersIds($condition->getMemberSearch(), $condition->getContainsPcpIds());
        $fullColumn = mb_strpos($column, '.') !== false ? $column : $builder->getQuery()->from . '.' . $column;
        if (count($foundMemberIds)) {
            if ($isRawQueryExpected) {
                $builder->whereRaw("$column IN (" . join(', ', Id::mapArrayValueToHexadecimal($foundMemberIds)) .")");
            } else {
                $builder->whereIn($fullColumn, Id::batchCreate($foundMemberIds));
            }
        } else {
            $builder->whereRaw('FALSE');
        }
    }
}
