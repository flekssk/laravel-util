<?php

declare(strict_types=1);

namespace FKS\Repositories\SearchQueryBuilders\SQL;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use FKS\Helpers\SearchComponent\SearchComponentConfigHelper;
use FKS\Repositories\ColumnParamMap;
use FKS\Repositories\SearchQueryBuilders\BuilderInterface;
use FKS\ValueObjects\Id;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;

class ContainsQueryBuilder implements BuilderInterface
{
    /**
     * @param ContainsCondition $condition
     */
    public function applyCondition(\Illuminate\Database\Query\Builder $builder, $condition, ColumnParamMap|string $column = null): void
    {
        $ids = $this->prepareValues($condition);
        $filterHasNullValue = in_array(null, $condition->getValues(), true);
        if (empty($ids) && !$filterHasNullValue) {
            return;
        }

        $builder->where(function ($query) use ($condition, $ids, $column, $filterHasNullValue) {
            $isRawQueryExpected = SearchComponentConfigHelper::isUseRawQueryStatements();
            /** @var $query Builder */
            if ($column instanceof ColumnParamMap) {
                $filterParamParts = explode('.', $condition->getFilterParam());
                if ($isRawQueryExpected && $condition->getType() !== ContainsCondition::TYPE_STRING) {
                    $column = $column->tableColumn;
                    if ( $condition->getType() === ContainsCondition::TYPE_BOOLEAN ) {
                        $column = "SAFE_CAST($column as BOOLEAN)";
                    }
                    $query->whereRaw("$column->tableName.$column = '$filterParamParts[1]'");
                } else {
                    $query->where($column->tableName . '.' . $column->tableColumn, $filterParamParts[1]);
                }
                $column = $column->tableName . '.' . $column->tableValuesColumn;
            }
            if ($condition->isContains()) {
                if ($condition->isInArray()) {
                    $query->where(static function (Builder $builder) use ($ids, $column, $condition) {
                        foreach ($ids as $id) {
                            if ($condition->isStringArray === true) {
                                $builder->orWhereRaw("$column LIKE '%\"$id\"%'");
                            } elseif ($condition->isBytes()) {
                                $id = Id::mapArrayValueToHexadecimal([$id]);
                                $builder->whereRaw("$id[0] in unnest($column)");
                            } else {
                                $builder->whereRaw("$id in unnest($column)");
                            }
                        }
                    });
                } else {
                    if ($ids) {
                        if ($condition->isBytes()) {
                            $query->whereRaw("$column IN (" . join(', ', Id::mapArrayValueToHexadecimal($ids)) . ")");
                        } else {
                            if ($isRawQueryExpected && $condition->getType() !== ContainsCondition::TYPE_STRING) {
                                if ($condition->isBoolean()) {
                                    if (count($ids) === 1) {
                                        $boolValue = $ids[0] ? 'TRUE' : 'FALSE';
                                        $query->whereRaw("SAFE_CAST($column AS BOOLEAN) = $boolValue");
                                    }
                                } elseif ($condition->getType() === ContainsCondition::TYPE_HEX_STRING) {
                                    $query->whereRaw("$column IN (\"" . join('", "', $ids) . '")');
                                } else {
                                    $query->whereRaw("$column IN (" . join(', ', $ids) . ")");
                                }
                            } else {
                                if ($condition->isBoolean()) {
                                    $boolValue = $ids[0] ? 'TRUE' : 'FALSE';
                                    $query->where("SAFE_CAST($column as BOOLEAN)", $boolValue);
                                } else if ($condition->isString() && $condition->isCaseInsensitive()) {
                                    $query->whereIn(
                                        DB::raw('LOWER('. $column .')'),
                                        array_map(fn ($id) => strtolower($id), $ids)
                                    );
                                } else {
                                    $query->whereIn($column, $ids);
                                }
                            }
                        }
                    }
                }
                if ($filterHasNullValue) {
                    $stringTypes = [ContainsCondition::TYPE_STRING];
                    if (in_array($condition->getType(), $stringTypes)) {
                        $query->orWhere($column, '');
                    }

                    $query->orWhereNull($column);
                }
            } else {
                if ($condition->isInArray()) {
                    $query->where(static function (Builder $builder) use ($ids, $column, $condition) {
                        foreach ($ids as $id) {
                            if ($condition->isBytes()) {
                                $id = Id::create($id)->getHexadecimal();
                            }

                            $builder->whereRaw("$id not in unnest($column)");
                        }
                    });
                } else {
                    if ($ids) {
                        if ($condition->isBytes()) {
                            $query->whereRaw("$column NOT IN (" . join(', ', Id::mapArrayValueToHexadecimal($ids)) . ")");
                        } else {
                            if ($isRawQueryExpected && $condition->getType() !== ContainsCondition::TYPE_STRING) {
                                $query->whereRaw("$column NOT IN (" . join(', ', $ids) . ")");
                            } else {
                                $query->whereNotIn($column, $ids);
                            }
                        }
                    }
                }
                if ($filterHasNullValue) {
                    $query->whereNotNull($column);
                } else {
                    $query->orWhereNull($column);
                }
            }
        });
    }

    protected function prepareValues(ContainsCondition $condition): array
    {
        $values = $condition->getValues();

        if (empty($values)) {
            return [];
        }

        $values = array_unique(
            array_values(
                array_filter($values, function ($value) {
                    return !empty($value) || $value === 0 || $value === false;
                })
            )
        );

        if ($condition->isBytes()) {
            $values = Id::batchCreate($values);
        }
        if ($condition->isString()) {
            $values = array_map(static fn($item) => (string) $item, $values);
        }
        if ($condition->isInteger()) {
            $values = array_map(static fn($item) => (int) $item, $values);
        }
        if ($condition->isBoolean()) {
            $values = array_map(static fn($item) => (bool) $item, $values);
        }
        if ($condition->isHexString()) {
            $values = Id::mapArrayValueToHex($values);
        }

        return $values;
    }
}
