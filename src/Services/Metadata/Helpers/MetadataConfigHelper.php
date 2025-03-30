<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Helpers;

use FKS\Services\Serializer\SerializerFacade;
use Illuminate\Database\Eloquent\Model;
use FKS\Services\Metadata\MetadataConfig;

class MetadataConfigHelper
{
    private static array $loadedConfigs = [];

    /**
     * @param class-string<Model> $modelClass
     */
    public static function getModelConfig(string $modelClass): MetadataConfig
    {
        if (!isset(self::$loadedConfigs[$modelClass])) {
            $config = config("metadata.entities")[$modelClass] ?? null;

            if ($config === null) {
                throw new \Exception(
                    sprintf(
                        'Models config for %s model not defined in config/metadata.php.',
                        $modelClass,
                    )
                );
            }
            if (isset($config['mutators']) && is_array($config['mutators']) && $config['mutators'] !== []) {
                $config['mutators'] = array_map(static fn (string $class) => app($class),$config['mutators']);
            }

            self::$loadedConfigs[$modelClass] = SerializerFacade::deserializeFromArray($config, MetadataConfig::class);
        }

        return self::$loadedConfigs[$modelClass];
    }
}
