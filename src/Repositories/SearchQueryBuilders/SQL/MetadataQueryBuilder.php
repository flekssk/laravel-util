<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\Metadata\Helpers\MetadataConfigHelper;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;
use FKS\ValueObjects\SearchConditions\Conditions\EmptyOrNullCondition;
use FKS\ValueObjects\SearchConditions\Conditions\MetadataCondition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;
use FKS\ValueObjects\SearchConditions\Conditions\SearchCondition;
use Tests\Provisions\Database\Spanner\Query\SpannerJoinClause as TestSpannerJoinClause;

class MetadataQueryBuilder implements BuilderInterface
{
    /**
     * @param MetadataCondition $condition
     */
    public function applyCondition($builder, $condition, ColumnParamMap|string $column = null): void
    {
        if (!is_a($builder::class, Builder::class, true)) {
            throw new Exception(
                sprintf(
                    'Builder passed to %s should be instance of %s',
                    self::class,
                    Builder::class,
                )
            );
        }

        $config = MetadataConfigHelper::getModelConfig($builder->getModel()::class);

        $subJoin = DB::query()
            ->from($config->table)
            ->select("$config->table.$config->entityPrimaryKey")
            ->where(static function (QueryBuilder $builder) use ($config, $condition) {
                foreach ($condition->subConditions->all() as $metadataKey => $conditions) {
                    $builder->orWhere(
                        static function (QueryBuilder $builder) use (
                            $config,
                            $metadataKey,
                            $conditions
                        ) {
                            if (SearchComponentConfigHelper::isUseRawQueryStatements()) {
                                $builder->whereRaw("$config->table.$config->metadataKeyFieldName = '$metadataKey'");
                            } else {
                                $builder->where("$config->table.$config->metadataKeyFieldName", $metadataKey);
                            }
                            foreach ($conditions as $subCondition) {
                                $queryBuilder = match ($subCondition::class) {
                                    NumericCondition::class => new NumericQueryBuilder(),
                                    ContainsCondition::class => new ContainsQueryBuilder(),
                                    SearchCondition::class => new SearchQueryBuilder(),
                                    DateRangeCondition::class => new DateRangeQueryBuilder(),
                                    EmptyOrNullCondition::class => new EmptyOrNullQueryBuilder(),
                                    default => throw new Exception(
                                        'Can`t process condition ' . $subCondition::class
                                    ),
                                };

                                if ($queryBuilder instanceof ContainsQueryBuilder && $subCondition->getType() === $subCondition::TYPE_INTEGER) {
                                    $subCondition->setType($subCondition::TYPE_INTEGER_METADATA);
                                }
                                $queryBuilder->applyCondition(
                                    $builder,
                                    $subCondition,
                                    $config->metadataValueFieldName
                                );
                            }
                        }
                    );
                }
            })
            ->groupBy("$config->table.$config->entityPrimaryKey")
            ->havingRaw("count(1) = {$condition->subConditions->count()}");
        $joinMethod = $condition->applyRightJoin ? 'rightJoinSub' : 'joinSub';
        if ($condition->applyWhere) {
            $builder->whereIn("$config->entityTable.$config->entityPrimaryKey", $subJoin);
        } else {
            $builder->$joinMethod(
                $subJoin,
                'TEMP',
                static function (SpannerJoinClause|TestSpannerJoinClause $join) use ($config, $condition) {
                    if ($condition->applyHashJoin) {
                        $join->joinMethod(SpannerJoinMethodsEnum::JOIN_METHOD_HASH_JOIN->value);
                    }
                    $join->on("TEMP.$config->entityPrimaryKey", "$config->entityTable.$config->entityPrimaryKey");
                }
            );
        }
    }
}
