<?php

namespace Tests\Provisions\Http\Request;

use FKS\Http\Requests\FilteringDefinitions;
use FKS\Http\Requests\SearchRequest;

class TestPaginatorImplementationRequest extends SearchRequest
{
    public static function getAvailableFields(): array
    {
        return [];
    }

    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        return FilteringDefinitions::create(function (FilteringDefinitions $ruleBuilder) {
        });
    }

    public static function getSortingDefinitions(): array
    {
        return [];
    }

    public function getPerPageParameterName(): string
    {
        return 'limit';
    }

    public function getPageParameterName(): string
    {
        return 'offset';
    }

    public function getPage(): int
    {
        $offset = $this->get($this->getPageParameterName());
        $limit = $this->get($this->getPerPageParameterName());

        if($offset && $limit){
            return ceil($offset/$limit);
        }

        return 1;
    }

    public function getPerPage(): int
    {
        return $this->get($this->getPerPageParameterName()) ?? 100;
    }
}
