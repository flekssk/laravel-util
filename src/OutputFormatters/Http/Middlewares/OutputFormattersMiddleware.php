<?php

declare(strict_types=1);

namespace FKS\OutputFormatters\Http\Middlewares;

use Exception;
use Illuminate\Http\Request;
use FKS\OutputFormatters\Enums\OutputFormatEnum;

class OutputFormattersMiddleware
{
    public function handle(Request $request, callable $next)
    {
        $format = $request->get('outputFormat');

        if ($format === null) {
            throw new Exception('Output format is not specified');
        }

        $formatEnum = OutputFormatEnum::tryFrom($format);

        if ($formatEnum === null) {
            throw new Exception("Output format $format is not supported");
        }

        app()->instance(OutputFormatEnum::class, $formatEnum);

        return $next($request);
    }
}
