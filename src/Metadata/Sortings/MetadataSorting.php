<?php

declare(strict_types=1);

namespace FKS\Metadata\Sortings;

use FKS\Metadata\Helpers\MetadataConfigHelper;
use FKS\Metadata\MetadataConfig;
use FKS\Search\Repositories\SortingInterface;

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
