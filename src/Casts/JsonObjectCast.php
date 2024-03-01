<?php

declare(strict_types=1);

namespace FKS\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use FKS\Helpers\SpannerJsonHelper;

/**
 * Add to Model: protected $casts = [ 'json_column_name' => JsonObjectCast::class, ];
 */
class JsonObjectCast
{
    private const TEMPLATE_DB_UPDATE = "JSON '###JSON###'";
    private const TEMPLATE_DECODE_REGEXP = "~^JSON '(.*)'$~";

    public function get(Model $model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        return SpannerJsonHelper::fromJson($value);
    }

    public function set(
        Model $model,
        string $key,
        ?array $value,
        array $attributes
    ): array {
        $expr = null;
        if ($value !== null) {
            $expr = new Expression(
                SpannerJsonHelper::toString($value)
            );
        }

        return [
            $key => $expr,
        ];
    }
}
