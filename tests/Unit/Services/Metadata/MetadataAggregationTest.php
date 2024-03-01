<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Metadata;

use Illuminate\Foundation\Testing\TestCase;
use FKS\Services\Metadata\Enums\MetadataAggregationTypeEnum;
use FKS\Services\Metadata\Enums\MetadataAggregationValueTypeEnum;
use Tests\CreatesApplication;
use Tests\Provisions\Database\Spanner\SpannerConnectionFaker;
use Tests\Provisions\Models\ModelHasMetadata;
use Tests\Provisions\Services\TestMetadataService;

class MetadataAggregationTest extends TestCase
{
    use CreatesApplication;

    /**
     * @dataProvider aggregationParameterProvider
     */
    public function testAggregation(
        MetadataAggregationTypeEnum $aggregationType,
        MetadataAggregationValueTypeEnum $valueType,
        string $expectedQuery,
    ) {
        SpannerConnectionFaker::fake();
        config()->set(
            'FKS-metadata',
            [
                'entities' => [
                    ModelHasMetadata::class => [
                        'table' => 'test_metadata',
                        'primary_key' => 'test_metadata_id',
                        'entity_table' => 'tests',
                        'entity_primary_key' => 'test_id',
                        'metadata_key_field_name' => 'test_metadata_key',
                        'metadata_value_field_name' => 'test_metadata_value',
                    ],
                ]
            ]
        );

        $metadataService = new TestMetadataService();

        $query = ModelHasMetadata::query();
        $metadataService->repository->buildAggregationQuery(
            $query,
            'test',
            $aggregationType,
            $valueType,
        );

        $this->assertEquals($expectedQuery, $query->toSql());
    }

    public function aggregationParameterProvider(): array
    {
        return [
            [
                MetadataAggregationTypeEnum::AVG,
                MetadataAggregationValueTypeEnum::INT,
                "select AVG(SAFE_CAST(test_metadata.test_metadata_value as INT64)) as result from `test` inner join  `test_metadata` on `tests`.`test_id` = `test_metadata`.`test_id` where test_metadata.test_metadata_key = 'test'",
            ],
            [
                MetadataAggregationTypeEnum::MAX,
                MetadataAggregationValueTypeEnum::DECIMAL,
                "select MAX(SAFE_CAST(test_metadata.test_metadata_value as NUMERIC)) as result from `test` inner join  `test_metadata` on `tests`.`test_id` = `test_metadata`.`test_id` where test_metadata.test_metadata_key = 'test'",
            ],
            [
                MetadataAggregationTypeEnum::MIN,
                MetadataAggregationValueTypeEnum::DECIMAL,
                "select MIN(SAFE_CAST(test_metadata.test_metadata_value as NUMERIC)) as result from `test` inner join  `test_metadata` on `tests`.`test_id` = `test_metadata`.`test_id` where test_metadata.test_metadata_key = 'test'",
            ],
        ];
    }
}