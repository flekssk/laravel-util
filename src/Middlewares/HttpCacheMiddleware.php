<?php

declare(strict_types=1);

namespace FKS\Middlewares;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HttpCacheMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return Response|JsonResponse
     * @throws Exception
     */
    public function handle(Request $request, Closure $next, int $cacheTTL = 3600)
    {
        $response = $next($request);
        $response->withHeaders([
            'Cache-Control' => 'public, max-age=' . cache('http-response-cache.max-age', $cacheTTL),
            'Cloudflare-CDN-Cache-Control' => 'max-age=' . cache('http-response-cache.max-age', $cacheTTL),
            'CDN-Cache-Control' => 'max-age=' . cache('http-response-cache.max-age', $cacheTTL),
        ]);

        return $response;
    }
}
