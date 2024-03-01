<?php

declare(strict_types=1);

namespace FKS\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MetadataValueCast
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (Str::isJson($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes
    ): mixed {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $value;
    }
}