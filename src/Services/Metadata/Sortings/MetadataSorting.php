<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Sortings;

use FKS\Repositories\SortingInterface;
use FKS\Services\Metadata\Helpers\MetadataConfigHelper;
use FKS\Services\Metadata\MetadataConfig;

class MetadataSorting implements SortingInterface
{
    public readonly MetadataConfig $config;

    public function __construct(public readonly string $modelClass)
    {
    }


    public function getSorts(): array
    {
        return [
            MetadataConfigHelper::getModelConfig($this->modelClass)->table
        ];
    }

    public function getExamples(): array
    {
        return [
            MetadataConfigHelper::getModelConfig($this->modelClass)->table . '.' . MetadataConfigHelper::getModelConfig($this->modelClass)->metadataKeyFieldName,
        ];
    }
}
