<?php

declare(strict_types=1);

namespace FKS\Search\Http\Middlewares;

use Illuminate\Http\Request;
use FKS\Search\Services\ConditionTransformerService;
use FKS\Search\ValueObjects\Conditions\Condition;

abstract class FilterApplyMiddleware
{

    public function __construct(private readonly ConditionTransformerService $conditionTransformerService)
    {
    }

    abstract public function shouldBeApplied(Request $request): bool;

    abstract public function getCondition(Request $request): Condition;

    public function handle(Request $request, callable $next)
    {
        if ($request->offsetExists('available_fields')) {
            if ($this->shouldBeApplied($request)) {
                $request->offsetSet(
                    'filter',
                    array_merge(
                        $request->filter ?? [],
                        $this->conditionTransformerService->transform($this->getCondition($request)),
                    )
                );
            }
        }


        return $next($request);
    }
}
