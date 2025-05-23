<?php

declare(strict_types=1);

namespace FKS\Search\Repositories\SearchQueryBuilders\MySQL;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use FKS\Metadata\Helpers\MetadataConfigHelper;
use FKS\Search\Helpers\SearchComponentConfigHelper;
use FKS\Search\Repositories\ColumnParamMap;
use FKS\Search\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Search\ValueObjects\Conditions\ContainsCondition;
use FKS\Search\ValueObjects\Conditions\DateRangeCondition;
use FKS\Search\ValueObjects\Conditions\EmptyOrNullCondition;
use FKS\Search\ValueObjects\Conditions\MetadataCondition;
use FKS\Search\ValueObjects\Conditions\NumericCondition;
use FKS\Search\ValueObjects\Conditions\SearchCondition;

class MetadataQueryBuilder implements BuilderInterface
{
    /**
     * @param MetadataCondition $condition
     */
    public function applyCondition(Builder $builder, $condition, ColumnParamMap|string $column = null): void
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
                static function (JoinClause $join) use ($config, $condition) {
                    $join->on("TEMP.$config->entityPrimaryKey", "$config->entityTable.$config->entityPrimaryKey");
                }
            );
        }
    }
}
