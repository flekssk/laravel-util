<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Models;

use FKS\Models\Model;
use FKS\Services\Metadata\Casts\MetadataValueCast;
use FKS\Services\Metadata\Helpers\FKSMetadataConfigHelper;
use FKS\Services\Metadata\MetadataConfig;

abstract class Metadata extends Model
{
    protected static ?MetadataConfig $config = null;

    protected $guarded = [];
    public $timestamps = false;

    /**
     * @param class-string<Metadata> $targetModelClass
     * @return \Colopl\Spanner\Eloquent\Model|self
     */
    public static function build(string $targetModelClass): self
    {
        $config = FKSMetadataConfigHelper::getModelConfig($targetModelClass);

        $reflection = new \ReflectionClass(
            new class () extends Metadata {

                public static array $onlyFields = [];

                public function __construct(array $attributes = [])
                {
                    if (self::$config !== null) {
                        $storeAsJson = self::$config->storeValueAsJson;
                        $this->table = self::$config->table;
                        $this->primaryKey = self::$config->primaryKey;
                        $this->casts = [
                            self::$config->entityPrimaryKey => 'spanner_binary_uuid',
                            self::$config->primaryKey => 'spanner_binary_uuid',
                            self::$config->metadataValueFieldName => MetadataValueCast::class
                        ];
                        self::$onlyFields = [
                            self::$config->entityPrimaryKey,
                            self::$config->primaryKey,
                            self::$config->metadataKeyFieldName,
                            self::$config->metadataValueFieldName,
                            'created_at',
                            'created_by',
                            'created_by_name',
                            'updated_at',
                            'updated_by',
                            'updated_by_name',
                        ];

                        $this->visible = [
                            self::$config->entityPrimaryKey,
                            self::$config->primaryKey,
                            self::$config->metadataKeyFieldName,
                            self::$config->metadataValueFieldName,
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                        ];
                    }

                    parent::__construct($attributes);
                }

                public function toArray(): array
                {
                    return $this->only(self::$onlyFields);
                }
            }
        );

        $reflection->setStaticPropertyValue('config', $config);

        return $reflection->newInstance();
    }

    public function getConfig(): MetadataConfig
    {
        return self::$config;
    }
}
