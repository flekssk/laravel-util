<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ActivityLog;

use Colopl\Spanner\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase;
use FKS\Services\ActivityLog\ActivityLogService;
use Tests\CreatesApplication;
use Tests\Unit\Services\ActivityLog\Formatters\TestFormatter;

class ActivityLogTest extends TestCase
{
    use CreatesApplication;

    public function testFormatters(): void
    {
        config()->set('FKS-activity-log.enable', true);
        config()->set('FKS-activity-log.activity-log-model-class', Model::class);
        config()->set('FKS-activity-log.entity-types', ['TEST' => 1]);
        config()->set('FKS-activity-log.activity-types', ['TEST' => 1]);
        config()->set('FKS-activity-log.activity-type-formatters',
            [
                1 => TestFormatter::class
            ]);

        /** @var ActivityLogService $service */
        $service = app(ActivityLogService::class);

        $testData = new Model();
        $testData->fillable(['payload']);
        $testData->payload = json_encode([], JSON_THROW_ON_ERROR);
        $testData->activity_type_id = 1;

        $result = $service->listPreparation([$testData]);

        $this->assertEquals(['test' => 'test'], $result[0]->payload);
    }
}
