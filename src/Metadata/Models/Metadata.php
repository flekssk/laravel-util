<?php

declare(strict_types=1);

namespace FKS\Metadata\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use FKS\Metadata\Casts\MetadataValueCast;
use FKS\Metadata\MetadataConfig;
use ReflectionClass;

abstract class Metadata extends Model
{
    use SoftDeletes;

    protected static ?MetadataConfig $config = null;

    protected $guarded = [];
    public $timestamps = false;

    public static function build(MetadataConfig $config): self
    {
        $reflection = new ReflectionClass(
            new class () extends Metadata {

                public static array $onlyFields = [];

                public function __construct(array $attributes = [])
                {
                    if (self::$config !== null) {
                        $only = [
                            self::$config->entityPrimaryKey,
                            self::$config->primaryKey,
                            self::$config->metadataKeyFieldName,
                            self::$config->metadataValueFieldName,
                        ];

                        if (!self::$config->onlyMetadataKeys) {
                            $only = array_merge(
                                $only,
                                [
                                    'created_at',
                                    'created_by',
                                    'created_by_name',
                                    'updated_at',
                                    'updated_by',
                                    'updated_by_name',
                                ]
                            );
                        }

                        $this->table = self::$config->table;
                        $this->primaryKey = self::$config->primaryKey;
                        $this->casts = [
                            self::$config->entityPrimaryKey => 'spanner_binary_uuid',
                            self::$config->primaryKey => 'spanner_binary_uuid',
                            self::$config->metadataValueFieldName => MetadataValueCast::class,
                            'created_by' => 'spanner_binary_uuid',
                            'updated_by' => 'spanner_binary_uuid',
                        ];
                        self::$onlyFields = $only;

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
