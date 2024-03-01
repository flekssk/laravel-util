<?php

declare(strict_types=1);

namespace Tests\Unit\Repository;

use Carbon\Carbon;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use FKS\Http\Requests\RuleBuilders\TimestampRangeRuleBuilder;
use FKS\ValueObjects\Id;
use FKS\ValueObjects\SearchConditions\Conditions\Condition;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;
use FKS\ValueObjects\SearchConditions\Conditions\SearchCondition;
use FKS\ValueObjects\SearchConditions\Conditions\StartsWithCondition;
use FKS\ValueObjects\SearchConditions\SearchConditions;
use Tests\CreatesApplication;
use Tests\Provisions\Database\Spanner\SpannerConnectionFaker;
use Tests\Provisions\Http\Request\TestSearchRequest;
use Tests\Provisions\Repository\Provision\TestElasticSearchRepository;
use Tests\Provisions\Repository\Provision\TestRepository;

class ElasticRepositoryTest extends TestCase
{
    use CreatesApplication;

    /**
     * @dataProvider requestDataProvider
     */
    public function testSearch(array $requestData, string $expectedQuery): void
    {
        $testRequest = new TestSearchRequest($requestData);
        $repository = new TestElasticSearchRepository(ClientBuilder::create()->build());
        $repository->search($testRequest->getSearchConditions());
        $this->assertEquals($expectedQuery, json_encode($repository->getQueryBuilder()->getQuery()));
    }

    public function requestDataProvider(): array
    {
        return [
            [
                [
                    'filter' => [
                        'task_id' => [
                            'contains' => [
                                '5b493f3a-d29c-4158-96f1-97049642754a',
                            ],
                        ],
                        'collection_id' => [
                            'notcontains' => [
                                'b63ae98f-db4e-48c0-87c3-b327a06f575c',
                            ],
                        ],
                        'search_field' => 'test',
                    ],
                ],
                '{"index":"test_index","body":{"from":0,"size":20,"query":{"bool":{"filter":[{"bool":{"must_not":{"terms":{"collection_id":["b63ae98f-db4e-48c0-87c3-b327a06f575c"]}}}},{"terms":{"task_id":["5b493f3a-d29c-4158-96f1-97049642754a"]}}],"must":[{"match":{"search_field":"test"}}]}}}}',
            ],
            [
                [
                    'filter' => [
                        'task_id' => [
                            'contains' => [
                                '0513b054-feea-42b8-a901-0944f7448f01',
                                null,
                            ],
                        ],
                    ],
                ],
                '{"index":"test_index","body":{"from":0,"size":20,"query":{"bool":{"filter":[{"bool":{"should":[{"terms":{"task_id":["0513b054-feea-42b8-a901-0944f7448f01"]}},{"bool":{"must_not":{"exists":{"field":"task_id","boost":1}}}}]}}]}}}}',
            ],
            [
                [
                    'filter' => [
                        'task_id' => [
                            'notcontains' => [
                                '0513b054-feea-42b8-a901-0944f7448f01',
                                null,
                            ],
                        ],
                    ],
                ],
                '{"index":"test_index","body":{"from":0,"size":20,"query":{"bool":{"filter":[{"bool":{"should":[{"exists":{"field":"task_id","boost":1}},{"bool":{"must_not":{"terms":{"task_id":["0513b054-feea-42b8-a901-0944f7448f01"]}}}}]}}]}}}}',
            ],
            [
                [
                    'filter' => [
                        'comment' => 'comment',
                        'pages_count' => [
                            'eq' => 1,
                            'ne' => 1,
                            'le' => 1,
                            'lt' => 1,
                            'gt' => 1,
                            'ge' => 1,
                        ],
                    ],
                ],
                '{"index":"test_index","body":{"from":0,"size":20,"query":{"bool":{"filter":[{"term":{"pages_count":{"value":1}}},{"range":{"pages_count":{"lte":1,"boost":1}}},{"range":{"pages_count":{"gte":1,"boost":1}}},{"range":{"pages_count":{"lt":1,"boost":1}}},{"range":{"pages_count":{"gt":1,"boost":1}}}],"must_not":[{"term":{"pages_count":{"value":1}}}],"must":[{"prefix":{"comment":"comment"}}]}}}}',
            ],
            [
                [
                    'filter' => [
                        'created_at' => [
                            'from' => $createdAtFrom = Carbon::yesterday()->format('Y-m-d'),
                            'to' => $createdAtTo = Carbon::now()->format('Y-m-d'),
                        ],
                        'uploaded_at' => [
                            'from' => $updatedAtFrom = Carbon::yesterday()->format(TimestampRangeRuleBuilder::FORMAT),
                            'to' => $updatedAtTo = Carbon::now()->format(TimestampRangeRuleBuilder::FORMAT),
                        ],
                    ],
                ],
                '{"index":"test_index","body":{"from":0,"size":20,"query":{"bool":{"filter":[{"range":{"created_at":{"gte":"' . $createdAtFrom . '","lte":"' . $createdAtTo . '","boost":1}}},{"range":{"uploaded_at":{"gte":"' . $updatedAtFrom . '","lte":"' . $updatedAtTo . '","boost":1}}}]}}}}',
            ],
            [
                [
                    'filter' => [
                        'bool_field' => true,
                        'uploaded_at' => [
                            'from' => "2023-12-23 00:00:00",
                            'to' => "2023-12-24 11:42:46",
                        ],
                    ],
                ],
                '{"index":"test_index","body":{"from":0,"size":20,"query":{"bool":{"filter":[{"term":{"bool_field":{"value":true}}},{"range":{"uploaded_at":{"gte":"2023-12-23 00:00:00","lte":"2023-12-24 11:42:46","boost":1}}}]}}}}',
            ],
        ];
    }
}