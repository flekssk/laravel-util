<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Traits;

trait SchemaTrait
{
    public static function getSchemaName(): string
    {
        return str_replace('Schema', '', class_basename(self::class));
    }
}