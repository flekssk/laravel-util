<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Settings;

abstract class SearchSetting
{
    abstract public static function getName(): string;
    public function validationRules(string $prefix = ''): array
    {
        return [];
    }
}
