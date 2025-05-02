<?php

declare(strict_types=1);

namespace FKS\Metadata;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use FKS\Search\Repositories\AbstractColumnParam;

class ColumnParamMetadataSort extends AbstractColumnParam
{
    public function __construct(
        public string $baseTableName,
        public string $baseTablePrimaryKey,
        public string $tableName,
        public string $tablePrimaryKey,
        public string $keyColumnName,
        public string $valueColumnName,
    ) {
    }

    public function getSortColumn($sortDefinition): string|Expression
    {
        return $this->getTableAlias($sortDefinition) . '.' . $this->valueColumnName;
    }

    /**
     * @param Builder $builder
     * @param $sortDefinition
     * @return void
     */
    public function applySortConditions($builder, $sortDefinition) : void {
        $builder->leftJoin($this->tableName, function (JoinClause $join) use ($sortDefinition) {
            $tableAlias = $this->getTableAlias($sortDefinition);
            $join->asAlias($tableAlias);
            $join->on(
                $tableAlias . '.' . $this->tablePrimaryKey,
                 $this->baseTableName . '.' . $this->baseTablePrimaryKey
            );
            $keyColumnValue = $this->getKeyColumnValue($sortDefinition);
            if ($keyColumnValue) {
                $join->on(
                    $tableAlias .  '.' . $this->keyColumnName,
                    DB::raw(sprintf("'%s'", $keyColumnValue))
                );
            }
        });
    }

    private function getKeyColumnValue(array $sortDefinition): ?string
    {
        preg_match(
            '/(\w+)\.(?<keyColumnValue>\w+)/',
            $sortDefinition['field'] ?? '',
            $matches
        );

        return $matches['keyColumnValue'] ?? null;
    }

    private function getTableAlias(array $sortDefinition): string
    {
        $value = $this->getKeyColumnValue($sortDefinition);
        if (!$value) {
            return $this->tableName;
        }

        return $this->tableName . '_' . $this->getKeyColumnValue($sortDefinition);
    }
}
