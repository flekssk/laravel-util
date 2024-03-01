<?php

declare(strict_types=1);

namespace FKS\Api;

use App\Helpers\ConstantsHelper;
use Illuminate\Support\Facades\Cache;

abstract class ApiCachedClient extends ApiClient
{
    public const API_CALLS_CACHE_TTL = 3600 * 24;

    abstract public static function getServiceCommonCacheTag(): string;

    public function wrapToCache(callable $dataAccessor, string $cacheKey, array $tags = [], ?int $ttl = null): mixed
    {
        $tags[] = static::getServiceCommonCacheTag();

        if (is_null($ttl)) {
            $ttl = (int) self::API_CALLS_CACHE_TTL;
        }

        return Cache::tags($tags)
            ->remember($cacheKey, $ttl, $dataAccessor);
    }

    /**
     * @template T
     * @psalm-param callable(): T $dataAccessor
     * @return T
     */
    public function walkPagesAndWrapToCache(
        callable $dataAccessor,
        string $cacheKey,
        array $tags = [],
        ?int $ttl = null,
        int $page = 1,
        int $limit = 1000
    ) {
        $tags[] = static::getServiceCommonCacheTag();

        if (is_null($ttl)) {
            $ttl = self::API_CALLS_CACHE_TTL;
        }

        return Cache::tags($tags)
            ->remember(
                $cacheKey,
                $ttl,
                function () use ($dataAccessor, $page, $limit) {
                    return $this->walkPages($dataAccessor, $page, $limit);
                }
            );
    }
}
