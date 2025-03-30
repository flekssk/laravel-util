<?php

declare(strict_types=1);

namespace FKS\Repositories;


use Illuminate\Contracts\Database\Query\Expression;

class ColumnParamMap extends AbstractColumnParam
{
    public function __construct(
        public string $tableName,
        public string $tableColumn,
        public ?string $tableValuesColumn = null,
    ) {
    }

    public function getSortColumn($sortDefinition): string|Expression
    {
        return $this->tableName . '.' . $this->tableValuesColumn;
    }

    public function applySortConditions($builder, $sortDefinition): void
    {
        if (!$this->tableValuesColumn) {
            return;
        }

        $fieldParts = explode('.', $sortDefinition['field']);
        $sortingTableColumnName = $fieldParts[1] ?? null;

        $builder->where($this->tableName . '.' . $this->tableColumn, $sortingTableColumnName);
    }
}
