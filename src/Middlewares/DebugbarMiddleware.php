<?php

declare(strict_types=1);

namespace FKS\Middlewares;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebugbarMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (
            !app()->environment('production')
            && $request->get('debugbar', false)
            && app('debugbar')->isEnabled()
            && $response instanceof JsonResponse
            && app()->bound('debugbar')
            && is_object($response->getData())
        ) {
            return new JsonResponse(
                array_merge(
                    $response->getData(true),
                    [
                        '_debugbar' => app('debugbar')->getData(),
                    ]
                )
            );
        }

        return $response;
    }
}