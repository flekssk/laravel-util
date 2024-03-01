<?php

namespace Tests\Unit\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;
use FKS\ValueObjects\SearchConditions\Conditions\MetadataCondition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;
use FKS\ValueObjects\SearchConditions\Conditions\StartsWithCondition;
use Tests\CreatesApplication;
use Tests\Provisions\Http\Request\TestSearchRequest;
use Webmozart\Assert\Assert;

class SearchRequestTest extends TestCase
{
    use CreatesApplication;

    /**
     * @covers \FKS\Http\Requests\SearchRequest::filteringRules
     * @covers \FKS\Http\Requests\SearchRequest::rules
     * @return void
     */
    public function testCheckRules(): void
    {
        $rules = (new TestSearchRequest())->filteringRules();

        $expected = [
            "filter.user_id" => "array",
            "filter.user_id.contains" => "array",
            "filter.user_id.contains.*" => "uuid_or_hex",
            "filter.user_id.notcontains" => "array",
            "filter.user_id.notcontains.*" => "uuid_or_hex",
            "filter.user_id_nullable" => "array",
            "filter.user_id_nullable.contains" => "array",
            "filter.user_id_nullable.contains.*" => "uuid_or_hex|nullable",
            "filter.user_id_nullable.notcontains" => "array",
            "filter.user_id_nullable.notcontains.*" => "uuid_or_hex|nullable",
            "filter.user_id_only_contains.contains.*" => "uuid_or_hex",
            "filter.type_id" => "array",
            "filter.type_id.contains" => "array",
            "filter.type_id.contains.*" => "integer",
            "filter.type_id.notcontains" => "array",
            "filter.type_id.notcontains.*" => "integer",
            "filter.simple_string_field" => "array",
            "filter.simple_string_field.contains" => "array",
            "filter.simple_string_field.contains.*" => "string",
            "filter.simple_string_field.notcontains" => "array",
            "filter.simple_string_field.notcontains.*" => "string",
            "filter.numeric_field" => "array",
            "filter.numeric_field.eq" => "numeric|nullable",
            "filter.numeric_field.ne" => "numeric|nullable",
            "filter.numeric_field.le" => "numeric|nullable",
            "filter.numeric_field.lt" => "numeric|nullable",
            "filter.numeric_field.gt" => "numeric|nullable",
            "filter.numeric_field.ge" => "numeric|nullable",
            "filter.integer_field" => "array",
            "filter.integer_field.eq" => "integer|nullable",
            "filter.integer_field.ne" => "integer|nullable",
            "filter.integer_field.le" => "integer|nullable",
            "filter.integer_field.lt" => "integer|nullable",
            "filter.integer_field.gt" => "integer|nullable",
            "filter.integer_field.ge" => "integer|nullable",
            'filter.created_at_date.from' => 'date_format:Y-m-d|required_with:filter.created_at_date|before_or_equal:filter.created_at_date.to|after:1900-01-01|before:2100-01-01',
            'filter.created_at_date.to' => 'date_format:Y-m-d|required_with:filter.created_at_date|after_or_equal:filter.created_at_date.from|after:1900-01-01|before:2100-01-01',
            'filter.updated_at_timestamp' => [
                'array',
                function ($param, $value, $fail) {
                    if ($value === []) {
                        $fail("The $param.from and $param.to is required when $param is passed.");
                    }
                }
            ],
            'filter.updated_at_timestamp.from' => 'date_format:Y-m-d H:i:s|required_with:filter.updated_at_timestamp|before_or_equal:filter.updated_at_timestamp.to|after:1900-01-01|before:2100-01-01',
            'filter.updated_at_timestamp.to' => 'date_format:Y-m-d H:i:s|required_with:filter.updated_at_timestamp|after_or_equal:filter.updated_at_timestamp.from|after:1900-01-01|before:2100-01-01',
            'filter.user_id_required' => 'array|required',
            'filter.user_id_required.contains' => 'array|required_without:filter.user_id_required.notcontains',
            'filter.user_id_required.contains.*' => 'uuid_or_hex',
            'filter.user_id_required.notcontains' => 'array|required_without:filter.user_id_required.contains',
            'filter.user_id_required.notcontains.*' => 'uuid_or_hex',
            'filter.int_in_array' => 'array',
            'filter.int_in_array.contains' => 'array',
            'filter.int_in_array.contains.*' => 'integer',
            'filter.int_in_array.notcontains' => 'array',
            'filter.int_in_array.notcontains.*' => 'integer',
            'filter.comment' => 'string|min:3',
            'filter.name_required' => 'string|min:3|required',
            'filter.user_id_only_contains' => 'array',
            'filter.user_id_only_contains.contains' => 'array',
            'filter.user_id_only_contains_required' => 'array|required',
            'filter.user_id_only_contains_required.contains' => 'array|required',
            'filter.user_id_only_contains_required.contains.*' => 'uuid_or_hex|required',
            'filter.user_id_only_contains.notcontains' => 'prohibited',
            'filter.user_id_only_contains_required.notcontains' => 'prohibited',
            'filter.search' => 'nullable|min:3|max:100',
            'filter.search_field' => 'nullable|min:1|max:200',
            'filter.metadata.*.filter_type' => [
                'required',
                Rule::in(['contains_hex', 'contains_string', 'integer', 'search_string'])
            ],
            'filter.metadata.*.data' => [
                'required',
                function ($param, $value, $fail) {
                    if ($value === []) {
                        $fail("The $param.from and $param.to is required when $param is passed.");
                    }
                },
            ],
            'filter.string' => 'string|nullable',
            'filter.date' => 'date:Y-m-d|nullable',
            'filter.collection_id' => 'array',
            'filter.collection_id.contains' => 'array',
            'filter.collection_id.contains.*' => 'uuid_or_hex',
            'filter.collection_id.notcontains' => 'array',
            'filter.collection_id.notcontains.*' => 'uuid_or_hex',
            'filter.task_id' => 'array',
            'filter.task_id.contains' => 'array',
            'filter.task_id.contains.*' => 'uuid_or_hex',
            'filter.task_id.notcontains' => 'array',
            'filter.task_id.notcontains.*' => 'uuid_or_hex',
            'filter.task_id_nullable' => 'array',
            'filter.task_id_nullable.contains' => 'array',
            'filter.task_id_nullable.contains.*' => 'uuid_or_hex|nullable',
            'filter.task_id_nullable.notcontains' => 'array',
            'filter.task_id_nullable.notcontains.*' => 'uuid_or_hex|nullable',
            'filter.pages_count' => 'array',
            'filter.pages_count.eq' => 'numeric|nullable',
            'filter.pages_count.ne' => 'numeric|nullable',
            'filter.pages_count.le' => 'numeric|nullable',
            'filter.pages_count.lt' => 'numeric|nullable',
            'filter.pages_count.gt' => 'numeric|nullable',
            'filter.pages_count.ge' => 'numeric|nullable',
            'filter.bool_field' => 'boolean',
            'filter.created_at_date' => [
                'array',
                function ($param, $value, $fail) {
                    if ($value === []) {
                        $fail("The $param.from and $param.to is required when $param is passed.");
                    }
                }
            ],
            'filter.created_at' => [
                'array',
                function ($param, $value, $fail) {
                    if ($value === []) {
                        $fail("The $param.from and $param.to is required when $param is passed.");
                    }
                }
            ],
            'filter.created_at.from' => 'date_format:Y-m-d|required_with:filter.created_at|before_or_equal:filter.created_at.to|after:1900-01-01|before:2100-01-01',
            'filter.created_at.to' => 'date_format:Y-m-d|required_with:filter.created_at|after_or_equal:filter.created_at.from|after:1900-01-01|before:2100-01-01',
            'filter.uploaded_at' => [
                'array',
                function ($param, $value, $fail) {
                    if ($value === []) {
                        $fail("The $param.from and $param.to is required when $param is passed.");
                    }
                }
            ],
            'filter.uploaded_at.from' => 'date_format:Y-m-d H:i:s|required_with:filter.uploaded_at|before_or_equal:filter.uploaded_at.to|after:1900-01-01|before:2100-01-01',
            'filter.uploaded_at.to' => 'date_format:Y-m-d H:i:s|required_with:filter.uploaded_at|after_or_equal:filter.uploaded_at.from|after:1900-01-01|before:2100-01-01',
            'filter.metadata' => 'array',
            'filter.metadata.*.metadata_key' => 'required|string',
        ];

        $this->assertEquals($expected, $rules);
    }

    /**
     * @covers \FKS\Http\Requests\SearchRequest::getSearchConditions
     * @return void
     */
    public function testSearchRequest(): void
    {
        $userIdContains = Str::uuid()->toString();
        $typeIdNotContains = [33, 40];
        $searchText = 'text to search';
        $request = new TestSearchRequest();
        $data = [
            'available_fields' => ['test', 'test3'],
            'filter' => [
                'user_id' => [
                    'contains' => [$userIdContains],
                ],
                'type_id' => [
                    'notcontains' => $typeIdNotContains,
                ],
                'comment' => $searchText,
                'numeric_field' => [
                    'eq' => 30,
                ],
                'integer_field' => [
                    'ge' => 50,
                ],
                'simple_string_field' => [
                    'contains' => ['string_one', 'string_two']
                ],
                'created_at_date' => [
                    'from' => '2020-12-01',
                    'to' => '2020-12-02',
                ],
                'updated_at_timestamp' => [
                    'from' => '2018-12-10 12:24:33',
                    'to' => '2018-12-20 23:12:35',
                ],
                "metadata" => [
                    [
                        'filter_type' => 'integer',
                        'metadata_key' => 'entity_code',
                        'data' => [
                            'eq' => 1477559409
                        ]
                    ],
                    [
                        'filter_type' => 'contains_hex',
                        'metadata_key' => 'entity_id',
                        'data' => [
                            'contains' => [
                                'c33a0fe0db91417a952735a8b31706cb'
                            ]
                        ]
                    ]
                ],
            ],
            'sort' => [
                [
                    'field' => 'type_id',
                    'direction' => 'asc',
                ],
            ]
        ];
        $request->query->replace($data);
        $searchConditions = $request->getSearchConditions();
        $this->assertEmpty(array_diff($searchConditions->getAvailableFields(), $data['available_fields']));
        foreach ($searchConditions->getFilter() as $condition) {
            if (get_class($condition) === ContainsCondition::class && $condition->isBytes()) {
                $this->assertEquals($condition->getValues()[0], $userIdContains);
            }
            if (get_class($condition) === ContainsCondition::class && $condition->isInteger()) {
                $this->assertEmpty(array_diff($condition->getValues(), $typeIdNotContains));
            }
            if (get_class($condition) === StartsWithCondition::class && $condition->getValue()) {
                $this->assertEquals($condition->getValue(), $searchText);
            }
            if (get_class($condition) === ContainsCondition::class && $condition->isString()) {
                $this->assertEmpty(
                    array_diff($condition->getValues(), $data['filter']['simple_string_field']['contains'] ?? [])
                );
            }
            if (get_class($condition) === NumericCondition::class && $condition->isNumeric()) {
                $this->assertEquals($condition->getValue(), 30);
                $this->assertEquals($condition->getOperator(), 'eq');
                $this->assertEquals($condition->getMathematicalOperator(), '=');
            }
            if (get_class($condition) === NumericCondition::class && $condition->isInteger()) {
                $this->assertEquals($condition->getValue(), 50);
                $this->assertEquals($condition->getOperator(), 'ge');
                $this->assertEquals($condition->getMathematicalOperator(), '>=');
            }
            if (get_class($condition) === DateRangeCondition::class && $condition->isDate()) {
                Assert::eq(0, $condition->getFrom()->diff(Carbon::parse('2020-12-01'))->days);
                Assert::eq(0, $condition->getTo()->diff(Carbon::parse('2020-12-02'))->days);
            }
            if (get_class($condition) === DateRangeCondition::class && $condition->isTimestamp()) {
                Assert::true($condition->getFrom()->eq(Carbon::parse('2018-12-10 12:24:33')));
                Assert::true($condition->getTo()->eq(Carbon::parse('2018-12-20 23:12:35')));
            }
            if (is_a($condition, MetadataCondition::class)) {
                if ($condition->getType() === MetadataCondition::TYPE_INTEGER) {
                    $this->assertCount(1, $condition->subConditions);
                    $this->assertInstanceOf(NumericCondition::class, $condition->subConditions[0]);
                    $this->assertEquals(NumericCondition::TYPE_INTEGER_STRING, $condition->subConditions[0]->getType());
                    $this->assertEquals('1477559409', $condition->subConditions[0]->getValue());
                } elseif ($condition->getType() === MetadataCondition::TYPE_CONTAINS_HEX) {
                    $this->assertCount(1, $condition->subConditions);
                    $this->assertInstanceOf(ContainsCondition::class, $condition->subConditions[0]);
                    $this->assertEquals(ContainsCondition::TYPE_HEX_STRING, $condition->subConditions[0]->getType());
                    $this->assertEquals(['c33a0fe0db91417a952735a8b31706cb'],
                        $condition->subConditions[0]->getValues());
                }
            }
        }
        $this->assertEquals($searchConditions->getSort()[0]['direction'], 'asc');
        $this->assertEquals($searchConditions->getSort()[0]['field'], 'type_id');
        $this->assertEquals($searchConditions->getSort()[0]['field'], 'type_id');
    }
}
