<?php

declare(strict_types=1);

namespace FKS\Services\PdfGenerator;

use App\Helpers\ConstantsHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CacheService
{
    /**
     * @param array $data
     * @param string|null $apiKey
     * @return string|null
     */
    public function cacheData(
        array $data,
        ?string $apiKey
    ): ?string {
        if (!empty($data)) {
            $cacheKey = md5(
                json_encode($data) . $apiKey . Str::random(10)
            );
            Cache::put(
                $cacheKey,
                json_encode($data),
                ConstantsHelper::API_CALLS_CACHE_TTL
            );

            return $cacheKey;
        }

        return null;
    }
}
