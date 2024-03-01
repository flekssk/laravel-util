<?php

declare(strict_types=1);

namespace FKS\Helpers\SearchComponent;

use FKS\Contracts\FKSPaginatorInterface;
use FKS\Exceptions\FKSDomainException;
use FKS\ValueObjects\SearchConditions\PerPagePaginator;

class SearchComponentConfigHelper
{
    public static function isUseRawQueryStatements(): bool
    {
        $value = config('FKS-search.use_raw_query_statements', false);
        if (!is_bool($value)) {
            throw new FKSDomainException('The FKS-search.use_raw_query_statements config param must be boolean.');
        }

        return $value;
    }

    public static function getPaginatorClass(): string
    {
        $class = config('FKS-search.paginator', PerPagePaginator::class);

        if (!is_a($class, FKSPaginatorInterface::class, true)) {
            throw new FKSDomainException(
                sprintf("The FKS-search.paginator must contains class string which implements %s.", FKSPaginatorInterface::class)
            );
        }

        return $class;
    }
}
