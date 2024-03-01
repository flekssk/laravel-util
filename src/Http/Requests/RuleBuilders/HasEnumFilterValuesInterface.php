<?php

namespace FKS\Http\Requests\RuleBuilders;

interface HasEnumFilterValuesInterface
{
    public function getEnumValues(): array;
}
