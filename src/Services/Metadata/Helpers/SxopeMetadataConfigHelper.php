<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Helpers;

use Illuminate\Database\Eloquent\Model;
use FKS\Services\Metadata\MetadataConfig;
use FKS\Services\Serializer\FKSSerializerFacade;

class FKSMetadataConfigHelper
{
    private static array $loadedConfigs = [];

    /**
     * @param class-string<Model> $modelClass
     */
    public static function getModelConfig(string $modelClass): MetadataConfig
    {
        if (!isset(self::$loadedConfigs[$modelClass])) {
            $config = config("FKS-metadata.entities")[$modelClass] ?? null;

            if ($config === null) {
                throw new \Exception(
                    sprintf(
                        'Models config for %s model not defined in config/FKS-metadata.php.',
                        $modelClass,
                    )
                );
            }

            self::$loadedConfigs[$modelClass] = FKSSerializerFacade::deserializeFromArray($config, MetadataConfig::class);
        }

        return self::$loadedConfigs[$modelClass];
    }
}
