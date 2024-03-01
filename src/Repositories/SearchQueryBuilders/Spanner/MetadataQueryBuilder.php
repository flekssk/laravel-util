<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\Spanner;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\Services\Metadata\Helpers\FKSMetadataConfigHelper;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\MetadataCondition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;
use FKS\ValueObjects\SearchConditions\Conditions\SearchCondition;

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

        $config = FKSMetadataConfigHelper::getModelConfig($builder->getModel()::class);

        foreach ($condition->subConditions->all() as $metadataKey => $conditions) {
            $builder->whereExists(
                static function (\Illuminate\Database\Query\Builder $builder) use ($config, $metadataKey, $conditions) {
                    $builder->from($config->table)
                        ->whereRaw(
                            "$config->table.$config->entityPrimaryKey = $config->entityTable.$config->entityPrimaryKey"
                        );

                    $builder->where(
                        static function (\Illuminate\Database\Query\Builder $builder) use (
                            $config,
                            $metadataKey,
                            $conditions
                        ) {
                            $builder->where("$config->table.$config->metadataKeyFieldName", $metadataKey);
                            foreach ($conditions as $subCondition) {
                                $queryBuilder = match ($subCondition::class) {
                                    NumericCondition::class => new NumericQueryBuilder(),
                                    ContainsCondition::class => new ContainsQueryBuilder(),
                                    SearchCondition::class => new SearchQueryBuilder(),
                                    default => throw new Exception('Can`t process condition ' . $subCondition::class),
                                };

                                $queryBuilder->applyCondition($builder, $subCondition, $config->metadataValueFieldName);
                            }
                        }
                    );
                }
            );
        }
    }
}
