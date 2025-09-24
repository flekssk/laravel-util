<?php

declare(strict_types=1);

namespace FKS\Search\Helpers;

use DomainException;
use FKS\Search\Contracts\PaginatorInterface;
use FKS\Search\ValueObjects\PerPagePaginator;
use FKS\Search\ValueObjects\SearchComponentConfig;
use FKS\Serializer\SerializerFacade;

class SearchComponentConfigHelper
{
    private static ?SearchComponentConfig $config = null;

    public static function isUseRawQueryStatements(): bool
    {
        $value = config('search.use_raw_query_statements', false);
        if (!is_bool($value)) {
            throw new DomainException('The search.use_raw_query_statements config param must be boolean.');
        }

        return $value;
    }

    public static function getPaginatorClass(): string
    {
        $class = config('search.paginator', PerPagePaginator::class);

        if (!is_a($class, PaginatorInterface::class, true)) {
            throw new DomainException(
                sprintf("The search.paginator must contains class string which implements %s.", PaginatorInterface::class)
            );
        }

        return $class;
    }

    public static function getConfig()
    {
        if (self::$config === null) {
            self::$config = SerializerFacade::deserializeFromArray(config('search'), SearchComponentConfig::class);
        }

        return self::$config;
    }
}
