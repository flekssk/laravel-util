<?php

declare(strict_types=1);

namespace FKS\Metadata\Helpers;

use Exception;
use FKS\Serializer\SerializerFacade;
use Illuminate\Database\Eloquent\Model;
use FKS\Metadata\MetadataConfig;

class MetadataConfigHelper
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
                throw new Exception(
                    sprintf(
                        'Metadata config for %s model not defined in config/FKS-metadata.php.',
                        $modelClass,
                    )
                );
            }

            self::$loadedConfigs[$modelClass] = SerializerFacade::deserializeFromArray($config, MetadataConfig::class);
        }

        return self::$loadedConfigs[$modelClass];
    }
}
