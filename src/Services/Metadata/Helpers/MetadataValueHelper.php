<?php

declare(strict_types=1);

namespace FKS\Services\Metadata\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use FKS\Services\Metadata\MetadataConfig;

class MetadataValueHelper
{
    public static function toString(mixed $value)
    {
        if (
            is_int($value)
            || is_float($value)
        ) {
            return (string) $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        if (is_bool($value)) {
            return $value === true ? 'true' : 'false';
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s', 'UTC');
        }

        if ($value === null) {
            return '';
        }

        return $value;
    }

    public static function fromString(string $value): mixed
    {
        if (is_numeric($value)) {
            $isFloat = str_contains($value, '.');

            return $isFloat ? (float) $value : (int) $value;
        }

        if (Str::isJson($value)) {
            return json_decode($value, true);
        }

        if (in_array($value, ['true', 'false'])) {
            return $value === 'true';
        }

        return $value;
    }

    public static function applyMutators(MetadataConfig $config, string $key, mixed $value): mixed
    {
        foreach ($config->mutators as $mutator) {
            $value = $mutator->apply($key, $value);
        }

        return $value;
    }
}
