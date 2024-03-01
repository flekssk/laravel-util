<?php

declare(strict_types=1);

namespace Tests\Unit\Repository;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use FKS\Http\Requests\RuleBuilders\TimestampRangeRuleBuilder;
use FKS\Http\Requests\SortingRuleBuilders\Enums\SortAsEnum;
use FKS\Services\Metadata\Enums\MetadataFilterTypeEnum;
use FKS\Services\Serializer\FKSSerializer;
use FKS\Services\Serializer\FKSSerializerInterface;
use FKS\ValueObjects\Id;
use FKS\ValueObjects\SearchConditions\Conditions\Condition;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;
use FKS\ValueObjects\SearchConditions\Conditions\EqualsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\MetadataCondition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;
use FKS\ValueObjects\SearchConditions\Conditions\SearchCondition;
use FKS\ValueObjects\SearchConditions\Conditions\StartsWithCondition;
use FKS\ValueObjects\SearchConditions\SearchConditions;
use Tests\CreatesApplication;
use Tests\Provisions\Database\Spanner\SpannerConnectionFaker;
use Tests\Provisions\Http\Request\TestSearchRequest;
use Tests\Provisions\Repository\Provision\TestModel;
use Tests\Provisions\Repository\Provision\TestRepository;

class RepositoryTest extends TestCase
{
    use CreatesApplication;

    /**
     * @dataProvider requestDataProvider
     */
    public function testSearch(array $requestData, string $expectedQuery): void
    {
        config()->set(
            'FKS-metadata.entities',
            [
                TestModel::class => [
                    'table' => 'task_metadata',
                    'primary_key' => 'task_metadata_id',
                    'entity_table' => 'tasks',
                    'entity_primary_key' => 'task_id',
                    'metadata_key_field_name' => 'task_metadata_key',
                    'metadata_value_field_name' => 'task_metadata_value',
                ],
            ]
        );
        $this->app->bind(FKSSerializerInterface::class, FKSSerializer::class);
        SpannerConnectionFaker::fake();
        $testRequest = new TestSearchRequest($requestData);
        $repository = new TestRepository();
        $builder = $repository->search($testRequest->getSearchConditions(), returnBuilder: true);
        $expectedQuery = str_replace('`', '', strtolower($expectedQuery));
        $actualQuery = str_replace('`', '', strtolower($builder->toSql()));
        $this->assertEquals($expectedQuery, $actualQuery);
        $this->assetBindings($requestData, $testRequest->getSearchConditions(), $builder);
    }

    /**
     * @dataProvider requestWithRawQueryDataProvider
     */
    public function testSearchWithRawQueryExpected(array $requestData, string $expectedQuery): void
    {
        config()->set('FKS-search.use_raw_query_statements', true);
        SpannerConnectionFaker::fake();
        $testRequest = new TestSearchRequest($requestData);
        $repository = new TestRepository();
        $builder = $repository->search($testRequest->getSearchConditions(), returnBuilder: true);
        $expectedQuery = str_replace('`', '', strtolower($expectedQuery));
        $actualQuery = str_replace('`', '', strtolower($builder->toSql()));
        $this->assertEquals($expectedQuery, $actualQuery);
        $this->assetBindings($requestData, $testRequest->getSearchConditions(), $builder, true);
    }

    /**
     * @dataProvider invalidFiltersDataProvider
     */
    public function testSearchWithInvalidDataProvider(array $requestData, array $expectedErrors): void
    {
        $testRequest = new TestSearchRequest([], $requestData);
        $result = Validator::make($requestData, $testRequest->rules());
        $errors = $result->errors()->toArray();
        $this->assertCount(count($errors), $expectedErrors);

        foreach ($errors as $filed => $respondedFieldErrors) {
            $this->assertArrayHasKey($filed, $expectedErrors);
            $this->assertCount(count($expectedErrors[$filed]), $respondedFieldErrors);

            foreach ($respondedFieldErrors as $error) {
                $this->assertContains($error, $expectedErrors[$filed]);
            }
        }
    }

    public function assetBindings(
        array $requestFilters,
        SearchConditions $conditions,
        Builder $query,
        bool $expectedRawQueries = false
    ): void {
        $expectedBindings = [];
        foreach ($requestFilters['filter'] ?? [] as $filterName => $requestFilter) {
            $filterCondition = $conditions->getFilter()->getFilter($filterName);
            $this->assertNotNull($filterCondition);
            if ($filterCondition instanceof ContainsCondition && $filterCondition->isBytes()) {
                continue;
            }
            if ($filterCondition instanceof Collection) {
                foreach ($filterCondition as $item) {
                    $values = $this->getConditionValues($item);
                    $this->assertNotEquals([], $values, "Can`t resolve values for filter $filterName");
                    $expectedBindings = array_merge($expectedBindings, $values);
                }
            } else {
                $values = $this->getConditionValues($filterCondition);
                $assertMethod = $filterCondition instanceof EqualsCondition && $filterCondition->getData() === null
                    ?  'assertEquals'
                    : 'assertNotEquals';
                $this->$assertMethod([], $values, "Can`t resolve values for filter $filterName");
                if ($filterCondition instanceof SearchCondition) {
                    $values = ['%\\' . $values[0] . '\\%'];
                }
                $expectedBindings = array_merge($expectedBindings, $values);
            }
        }

        $actualBindings = [];
        foreach ($query->getBindings() as $item) {
            if ($item instanceof Bytes) {
                continue;
            } elseif ($item instanceof Carbon) {
                $actualBindings[] = $item->toISOString();
            } else {
                $actualBindings[] = $item;
            }
        }

        if ($expectedRawQueries) {
            $expectedBindings = array_filter($expectedBindings, static function ($value) {
                $isDateTime = false;
                try {
                        Carbon::make($value) ?? null;
                    $isDateTime = true;
                } catch (InvalidFormatException) {
                }

                if (is_string($value) && $isDateTime === false) {
                    return true;
                }

                return false;
            });
        }

        $this->assertEquals($expectedBindings, $actualBindings);
    }

    public function getConditionValues(Condition $condition): array
    {
        $values = [];
        if ($condition instanceof ContainsCondition) {
            if ($condition->getType() === ContainsCondition::TYPE_BYTES) {
                $values = array_map(static fn(Id $id) => $id->getHex(), Id::batchCreate($condition->getValues()));
            } else {
                $values = $condition->getValues();
            }
        }
        if ($condition instanceof StartsWithCondition) {
            $values[] = $condition->getValue();
        }
        if ($condition instanceof NumericCondition) {
            $values[] = $condition->getValue();
        }
        if ($condition instanceof SearchCondition) {
            $values[] = $condition->value;
        }
        if ($condition instanceof DateRangeCondition) {
            if ($condition->getType() === DateRangeCondition::TYPE_TIMESTAMP) {
                if ($condition->getFrom()) {
                    $values[] = $condition->getFrom()->toISOString();
                }
                if ($condition->getTo()) {
                    $values[] = $condition->getTo()->toISOString();
                }
            } else {
                if ($condition->getFrom()) {
                    $values[] = $condition->getFrom()->format('Y-m-d');
                }
                if ($condition->getTo()) {
                    $values[] = $condition->getTo()->format('Y-m-d');
                }
            }
        }
        if ($condition instanceof MetadataCondition) {
            foreach ($condition->subConditions->all() as $metadataKey => $subConditions) {
                $values[] = $metadataKey;
                foreach ($subConditions as $subCondition) {
                    if ($subCondition instanceof NumericCondition) {
                        continue;
                    } else if ($subCondition instanceof SearchCondition) {
                        $value = strtolower($subCondition->value);
                        $values[] = "%$value%";
                    } else {
                        $values = array_merge($values, $this->getConditionValues($subCondition));
                    }
                }
            }
        }
        if ($condition instanceof EqualsCondition) {
            $values = $condition->getData() !== null ? [$condition->getData()] : [];
        }
        return $values;
    }

    public static function requestDataProvider(): array
    {
        $userId = Id::create(Str::uuid()->toString());
        $userIdNotRequired = Id::create(Str::uuid()->toString());

        return [
            [
                [
                    'filter' => [
                        'string' => 'test',
                        'date' => Carbon::now()->format('Y-m-d'),
                    ],
                ],
                "select * from test_models where string = ? and date = ? limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'string' => null,
                        'date' => null,
                    ],
                ],
                "select * from test_models where string is null and date is null limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'user_id' => [
                            'contains' => [
                                $userId->getUuid()
                            ],
                        ],
                        'user_id_required' => [
                            'notcontains' => [
                                $userIdNotRequired->getUuid()
                            ],
                        ],
                        'search_field' => '\\test\\',
                        "metadata" => [
                            [
                                'filter_type' => MetadataFilterTypeEnum::TYPE_INTEGER->value,
                                'metadata_key' => 'entity_code',
                                'data' => [
                                    'eq' => 1477559409
                                ]
                            ],
                            [
                                'filter_type' => MetadataFilterTypeEnum::TYPE_CONTAINS_HEX->value,
                                'metadata_key' => 'entity_id',
                                'data' => [
                                    'contains' => [
                                        'c33a0fe0db91417a952735a8b31706cb'
                                    ]
                                ]
                            ],
                            [
                                'filter_type' => MetadataFilterTypeEnum::TYPE_CONTAINS_STRING->value,
                                'metadata_key' => 'string_metadata',
                                'data' => [
                                    'contains' => [
                                        'string value'
                                    ]
                                ]
                            ],
                            [
                                'filter_type' => MetadataFilterTypeEnum::TYPE_SEARCH_STRING->value,
                                'metadata_key' => 'searchable_metadata',
                                'data' => 'TESTsS',
                            ],
                        ],
                    ],
                ],
                "select * from `test_models` where (`user_id` in ({$userId->getHexadecimal()})) and (`user_id_required` not in ({$userIdNotRequired->getHexadecimal()}) or `user_id_required` is null) and REGEXP_REPLACE(lower(search_field), r'\s', '') like ? and exists (select * from task_metadata where task_metadata.task_id = tasks.task_id and (task_metadata.task_metadata_key = ? and safe_cast(task_metadata_value as int64) = 1477559409 )) and exists (select * from task_metadata where task_metadata.task_id = tasks.task_id and (task_metadata.task_metadata_key = ? and (task_metadata_value in (?)))) and exists (select * from task_metadata where task_metadata.task_id = tasks.task_id and (task_metadata.task_metadata_key = ? and (task_metadata_value in (?)))) and exists (select * from task_metadata where task_metadata.task_id = tasks.task_id and (task_metadata.task_metadata_key = ? and lower(task_metadata_value) like ?)) limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'comment' => 'comment',
                        'numeric_field' => [
                            'eq' => 1,
                            'ne' => 1,
                            'le' => 1,
                            'lt' => 1,
                            'gt' => 1,
                            'ge' => 1,
                        ],
                    ],
                ],
                "select * from `test_models` where STARTS_WITH(`comment`, ?) and `numeric_field` = ? and `numeric_field` != ? and `numeric_field` <= ? and `numeric_field` >= ? and `numeric_field` < ? and `numeric_field` > ? limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'created_at_date' => [
                            'from' => Carbon::yesterday()->format('Y-m-d'),
                            'to' => Carbon::now()->format('Y-m-d'),
                        ],
                        'updated_at_timestamp' => [
                            'from' => Carbon::yesterday()->format(TimestampRangeRuleBuilder::FORMAT),
                            'to' => Carbon::now()->format(TimestampRangeRuleBuilder::FORMAT),
                        ],
                    ],
                ],
                "select * from `test_models` where `created_at_date` between ? and ? and `updated_at_timestamp` between ? and ? limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'user_id_nullable' => [
                            'contains' => [
                                null
                            ]
                        ],
                    ],
                    'sort' => [
                        [
                            'field' => 'field1',
                            'direction' => 'asc',
                            'sort_as' => SortAsEnum::INTEGER->value,
                        ],
                        [
                            'field' => 'field2',
                            'direction' => 'asc',
                            'sort_as' => SortAsEnum::FLOAT->value,
                        ],
                    ],
                ],
                "select * from test_models where (user_id_nullable is null) order by safe_cast(field1 as int64) asc, safe_cast(field2 as float64) asc limit 20 offset 0"
            ],
        ];
    }

    public static function requestWithRawQueryDataProvider(): array
    {
        $userId = Id::create(Str::uuid()->toString());
        $userIdNotRequired = Id::create(Str::uuid()->toString());

        return [
            [
                [
                    'filter' => [
                        'user_id' => [
                            'contains' => [
                                $userId->getUuid()
                            ],
                        ],
                        'user_id_required' => [
                            'notcontains' => [
                                $userIdNotRequired->getUuid()
                            ],
                        ],
                        'search_field' => '\\test\\'
                    ],
                ],
                "select * from `test_models` where (`user_id` in ({$userId->getHexadecimal()})) and (`user_id_required` not in ({$userIdNotRequired->getHexadecimal()}) or `user_id_required` is null) and REGEXP_REPLACE(lower(search_field), r'\s', '') like ? limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'comment' => 'comment',
                        'numeric_field' => [
                            'eq' => 1,
                            'ne' => 1,
                            'le' => 1,
                            'lt' => 1,
                            'gt' => 1,
                            'ge' => 1,
                        ],
                    ],
                ],
                "select * from test_models where starts_with(comment, ?) and numeric_field = 1  and numeric_field != 1  and numeric_field <= 1  and numeric_field >= 1  and numeric_field < 1  and numeric_field > 1  limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'created_at_date' => [
                            'from' => '2024-01-25',
                            'to' => '2024-01-26',
                        ],
                        'updated_at_timestamp' => [
                            'from' => '2024-01-25 00:00:00',
                            'to' => '2024-01-26 04:49:54',
                        ],
                    ],
                ],
                "select * from test_models where created_at_date between '2024-01-25' and '2024-01-26' and updated_at_timestamp between '2024-01-25 00:00:00 utc' and '2024-01-26 04:49:54 utc' limit 20 offset 0"
            ],
            [
                [
                    'filter' => [
                        'created_at_date' => [
                            'from' => '',
                            'to' => '',
                        ],
                    ],
                ],
                "select * from test_models where created_at_date is null limit 20 offset 0"
            ],
        ];
    }

    public function invalidFiltersDataProvider(): array
    {
        return [
            [
                [
                    'available_fields' => [],
                    'filter' => [
                        'created_at' => [],
                        'uploaded_at' => ['from' => '', 'to' => Carbon::now()->format('Y-m-d H:i:s')],
                        'updated_at_timestamp' => ['from' => Carbon::now()->format('Y-m-d H:i:s'), 'to' => ''],
                    ],
                ],
                [
                    'available_fields' => [
                        'The available fields field is required.',
                    ],
                    'filter.user_id_required' => [
                        'The filter.user id required field is required.',
                    ],
                    'filter.user_id_required.contains' => [
                        'The filter.user id required.contains field is required when filter.user id required.notcontains is not present.',
                    ],
                    'filter.user_id_required.notcontains' => [
                        'The filter.user id required.notcontains field is required when filter.user id required.contains is not present.',
                    ],
                    'filter.user_id_only_contains_required' => [
                        'The filter.user id only contains required field is required.',
                    ],
                    'filter.user_id_only_contains_required.contains' => [
                        'The filter.user id only contains required.contains field is required.',
                    ],
                    'filter.name_required' => [
                        'The filter.name required field is required.',
                    ],
                    'filter.created_at' => [
                        'The filter.created_at.from and filter.created_at.to is required when filter.created_at is passed.',
                    ],
                    'filter.uploaded_at.from' => [
                        'The filter.uploaded at.from field is required when filter.uploaded at is present.',
                    ],
                    'filter.uploaded_at.to' => [
                        'The filter.uploaded at.to field must be a date after or equal to filter.uploaded at.from.',
                    ],
                    'filter.updated_at_timestamp.to' => [
                        'The filter.updated at timestamp.to field is required when filter.updated at timestamp is present.',
                    ],
                ]
            ]
        ];
    }
}