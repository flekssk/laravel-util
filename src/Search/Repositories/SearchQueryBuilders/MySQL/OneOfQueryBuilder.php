<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Illuminate\Database\Eloquent\Builder;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Search\ValueObjects\Conditions\SearchCondition;

class OneOfQueryBuilder implements BuilderInterface
{
    public function applyCondition(Builder $builder, $condition, string $column = null): void
    {
        $builder
            ->where(static function ($builder) use ($condition) {
                foreach ($condition->column as $subCondition) {
                    $queryBuilder = match ($subCondition::class) {
                        NumericCondition::class => new NumericQueryBuilder(),
                        ContainsCondition::class => new ContainsQueryBuilder(),
                        SearchCondition::class => new SearchQueryBuilder(),
                        DateRangeCondition::class => new DateRangeQueryBuilder(),
                        EmptyOrNullCondition::class => new EmptyOrNullQueryBuilder(),
                        SearchCondition::class => new SearchQueryBuilder(),
                        default => throw new \Exception(
                            'Can`t process condition ' . $subCondition::class
                        ),
                    };

                    if ($queryBuilder instanceof ContainsQueryBuilder && $subCondition->getType() === $subCondition::TYPE_INTEGER) {
                        $subCondition->setType($subCondition::TYPE_INTEGER_METADATA);
                    }

                    $builder->orWhere(
                        static function (Builder $builder) use ($subCondition, $condition, $queryBuilder) {
                            $queryBuilder->applyCondition(
                                $builder,
                                $subCondition,
                                $subCondition->getFilterParam()
                            );
                        }
                    );
                }
            });
    }
}
