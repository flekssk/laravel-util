<?php

namespace Tests\Provisions\Http\Request;

use FKS\Http\Requests\FilteringDefinitions;
use FKS\Http\Requests\SearchRequest;

class TestOneOfEnumSearchRequest extends SearchRequest
{
    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        return FilteringDefinitions::create(function (FilteringDefinitions $ruleBuilder) {

            $ruleBuilder->oneOfEnum('enum_field', TestEnum::class);
        });
    }

    public static function getAvailableFields(): array
    {
        return [
            'test_field'
        ];
    }

    public static function getSortingDefinitions(): array
    {
        return ['sort_field'];
    }


}
