<?php

declare(strict_types=1);

namespace FKS\Helpers;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class SpannerJsonHelper
{
    private const TEMPLATE_DB_UPDATE = "JSON '###JSON###'";
    private const TEMPLATE_DECODE_REGEXP = "~^JSON '(.*)'$~";

    public static function fromJson($value): array
    {
        if ($value instanceof Expression) {
            $value = $value->getValue(DB::getQueryGrammar());
        }

        if (preg_match(self::TEMPLATE_DECODE_REGEXP, $value, $m)) {
            $value = $m[1];
        }

        $value = str_replace('\\\\', '\\', $value);
        $value = str_replace('\u003F', '?', $value);

        return json_decode(
            $value,
            true,
            64,
            JSON_THROW_ON_ERROR + JSON_FORCE_OBJECT + JSON_HEX_APOS + JSON_UNESCAPED_SLASHES
        );
    }

    public static function toString(array $data): string
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR + JSON_FORCE_OBJECT + JSON_HEX_APOS + JSON_UNESCAPED_SLASHES);
        $json = str_replace('\\', '\\\\', $json);
        $json = str_replace('?', '\u003F', $json);

        return str_replace(
            '###JSON###',
            $json,
            self::TEMPLATE_DB_UPDATE
        );
    }
}
