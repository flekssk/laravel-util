<?php

declare(strict_types=1);

namespace FKS\Search\Http\Middlewares;

use Illuminate\Http\Request;
use FKS\Search\Services\ConditionTransformerService;
use FKS\Search\ValueObjects\Conditions\Condition;

abstract class FilterApplyMiddleware
{

    public function __construct(private readonly ConditionTransformerService $conditionTransformerService) {}

    abstract public function shouldBeApplied(Request $request): bool;
    abstract public function getCondition(string $name): Condition;

    public function handle(Request $request, callable $next, string $filter)
    {
        if ($this->shouldBeApplied($request)) {
            $request->offsetSet(
                'filters',
                array_merge(
                    $request->filters ?? [],
                    $this->conditionTransformerService->transform($this->getCondition($filter)),
                )
            );
        }

        return $next($request);
    }
}
