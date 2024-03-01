<?php

declare(strict_types=1);

namespace Tests\Provisions\Services;

use FKS\Services\Metadata\MetadataService;
use Tests\Provisions\Models\ModelHasMetadata;

class TestMetadataService extends MetadataService
{
    public static function getEntity(): string
    {
        return ModelHasMetadata::class;
    }
}