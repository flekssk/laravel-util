<?php

namespace Tests\Provisions\Http\Request;

use FKS\Http\Requests\FilteringDefinitions;
use FKS\Http\Requests\SearchRequest;

class TestContainsOneOfEnumSearchRequest extends SearchRequest
{
    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        return FilteringDefinitions::create(function (FilteringDefinitions $ruleBuilder) {
            $ruleBuilder->containsOneOfEnum('enum_field', TestEnum::class);
        });
    }

    public static function getAvailableFields(): array
    {
        return ['field'];
    }

    public static function getSortingDefinitions(): array
    {
        return [];
    }


}
