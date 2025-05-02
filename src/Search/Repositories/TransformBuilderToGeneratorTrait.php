<?php

declare(strict_types=1);

namespace FKS\Search\Repositories;

use Closure;
use Generator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Work with big data with predictable memory usage
 */
trait TransformBuilderToGeneratorTrait
{
    public function transformBuilderToGenerator(
        EloquentBuilder|QueryBuilder $queryBuilder,
        int $limit = 1000,
        ?Closure $chunkHandlerAfterFetch = null
    ): Generator {
        $offset = 0;
        do {
            $results = $queryBuilder->offset($offset)->limit($limit)->get();
            if ($chunkHandlerAfterFetch) {
                $results = $chunkHandlerAfterFetch($results);
            }
            foreach ($results as $result) {
                yield $result;
            }
            $offset += $limit;
        } while (count($results) === $limit);
    }
}
