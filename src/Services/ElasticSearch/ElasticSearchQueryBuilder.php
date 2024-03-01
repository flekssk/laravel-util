<?php

namespace FKS\Services\ElasticSearch;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class ElasticSearchQueryBuilder
{
    public const SECTION_MUST = 'must';
    public const SECTION_MUST_NOT = 'must_not';
    public const SECTION_FILTER = 'filter';
    public const SECTION_SHOULD = 'should';

    private array $query = [];
    private array $sort = [];
    private ?int $offset = null;
    private ?int $size = null;

    public function __construct(
        private readonly string $indexName
    ) {
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function where(string|callable $fieldName, $value = null, string $section = self::SECTION_FILTER): static
    {
        if (is_callable($fieldName)) {
            $builder = new ElasticSearchQueryBuilder($this->getIndexName());
            $fieldName($builder);

            $condition = $builder->getQuery()['body']['query'];
            unset($builder);
        } else {
            $condition = [
                'term' => [
                    $fieldName => [
                        'value' => $value,
                    ],
                ],
            ];
        }

        return $this->addCondition($condition, $section);
    }

    public function whereIn(string $fieldName, array $values, string $section = self::SECTION_FILTER): static
    {
        return $this->addCondition([
            'terms' => [
                $fieldName => $values,
            ],
        ], $section);
    }

    public function whereNotIn(string $fieldName, array $values, string $section = self::SECTION_FILTER): static
    {
        return $this->addCondition([
            'bool' => [
                'must_not' => [
                    'terms' => [
                        $fieldName => $values,
                    ],
                ],
            ],
        ], $section);
    }

    public function whereNotNull(string $fieldName, string $section = self::SECTION_FILTER): static
    {
        return $this->addCondition([
            'exists' => [
                'field' => $fieldName,
                'boost' => 1.0,
            ]
        ], $section);
    }

    public function whereNull(string $fieldName, string $section = self::SECTION_MUST): static
    {
        return $this->addCondition([
            'bool' => [
                'must_not' => [
                    'exists' => [
                        'field' => $fieldName,
                        'boost' => 1.0,
                    ],
                ],
            ],
        ], $section);
    }

    public function whereBetween(string $fieldName, array $values, string $section = self::SECTION_FILTER): static
    {
        return $this->range(
            $fieldName,
            [
                '>=' => $values[0],
                '<=' => $values[1],
            ],
            $section
        );
    }

    public function match(string $fieldName, string $value, string $section = self::SECTION_MUST): static
    {
        return $this->addCondition([
            'match' => [
                $fieldName => $value,
            ],
        ], $section);
    }

    public function prefix(string $fieldName, string $value, string $section = self::SECTION_MUST): static
    {
        return $this->addCondition([
            'prefix' => [
                $fieldName => $value,
            ],
        ], $section);
    }

    public function range(string $fieldName, array $conditions, string $section = self::SECTION_FILTER): static
    {
        $operators = [];
        foreach ($conditions as $operator => $value) {
            $operators[$this->getComparisonOperator($operator)] = $value;
        }
        return $this->addCondition([
            'range' => [
                $fieldName => [
                    ...$operators,
                    'boost' => 1.0,
                ],
            ],
        ], $section);
    }

    public function orderBy(string $fieldName, string $direction = 'asc'): static
    {
        $this->sort[] = [
            $fieldName => $direction,
        ];

        return $this;
    }

    public function getQuery(): array
    {
        $body = [];
        if (!is_null($this->offset)) {
            $body['from'] = $this->offset;
        }
        if (!is_null($this->size)) {
            $body['size'] = $this->size;
        }
        if ($this->sort) {
            $body['sort'] = $this->sort;
        }

        $body['query'] = [
            'bool' => empty($this->query) ?
                ['match_all' => (object) []]
                : $this->query
        ];

        return [
            'index' => $this->getIndexName(),
            'body' => $body,
        ];
    }

    public function limit(int $limit): static
    {
        $this->size = $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    private function getComparisonOperator(string $operator): string
    {
        return match ($operator) {
            '<' => 'lt',
            '<=' => 'lte',
            '>' => 'gt',
            '>=' => 'gte',
            default => throw new InvalidArgumentException('Invalid operator: ' . $operator),
        };
    }

    public function addCondition(array $condition, string $section): static
    {
        $this->array_push($this->query, $condition, $section);

        return $this;
    }

    private function array_push(&$array, $value, string $key)
    {
        $keys = explode('.', $key);
        $current = array_shift($keys);
        if (!isset($array[$current])) {
            $array[$current] = [];
        }
        if (empty($keys)) {
            $array[$current][] = $value;
        } else {
            $this->array_push($array[$current], $value, implode('.', $keys));
        }
    }
}
