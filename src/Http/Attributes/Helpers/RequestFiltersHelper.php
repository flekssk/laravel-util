<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Helpers;

use FKS\Http\Requests\RuleBuilders\BooleanRuleBuilder;
use FKS\Http\Requests\RuleBuilders\DateRangeRuleBuilder;
use FKS\Http\Requests\RuleBuilders\MemberSearchRuleBuilder;
use FKS\Http\Requests\RuleBuilders\MetadataRuleBuilder;
use FKS\Http\Requests\RuleBuilders\NumericRuleBuilder;
use FKS\Http\Requests\RuleBuilders\StartsWithRuleBuilder;
use FKS\Http\Requests\RuleBuilders\StringSearchRuleBuilder;
use FKS\Http\Requests\RuleBuilders\TimestampRangeRuleBuilder;

class RequestFiltersHelper
{
    public static array $filterBuilderToTypeMap = [
        BooleanRuleBuilder::class => 'boolean',
        NumericRuleBuilder::class => 'numeric',
        DateRangeRuleBuilder::class => 'string',
        TimestampRangeRuleBuilder::class => 'string',
        StartsWithRuleBuilder::class => 'string',
        MemberSearchRuleBuilder::class => 'string',
        StringSearchRuleBuilder::class => 'string',
        MetadataRuleBuilder::class => 'object',
    ];
    public static array $filterBuilderToExampleMap = [
        BooleanRuleBuilder::class => 'true',
        NumericRuleBuilder::class => '1',
        MemberSearchRuleBuilder::class => 'User Name',
        DateRangeRuleBuilder::class => '2021-01-01',
        TimestampRangeRuleBuilder::class => '2021-01-01 12:34:56',
        StartsWithRuleBuilder::class => 'Text to search for a match from the beginning',
    ];
    public static array $primitivesToExampleMap = [
        'integer' => 1,
        'uuid_or_hex' => 'd46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00',
        'boolean' => true,
        'string' => 'string',
    ];
}
