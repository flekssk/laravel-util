<?php

namespace FKS\Web\Http\Middleware;

use Illuminate\Support\Facades\Gate;

class Authorize
{
    public function handle($request, $next)
    {
        return $this->allowedToUseTinker()
            ? $next($request)
            : abort(403);
    }

    protected function allowedToUseTinker(): bool
    {
        if (! config('web-view.enabled')) {
            return false;
        }

        return Gate::check('viewWebTinker');
    }
}
