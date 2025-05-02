<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Helpers;

use FKS\Search\Requests\RuleBuilders\BooleanRuleBuilder;
use FKS\Search\Requests\RuleBuilders\DateRangeRuleBuilder;
use FKS\Search\Requests\RuleBuilders\MetadataRuleBuilder;
use FKS\Search\Requests\RuleBuilders\NumericRuleBuilder;
use FKS\Search\Requests\RuleBuilders\StartsWithRuleBuilder;
use FKS\Search\Requests\RuleBuilders\StringSearchRuleBuilder;
use FKS\Search\Requests\RuleBuilders\TimestampRangeRuleBuilder;

class RequestFiltersHelper
{
    public static array $filterBuilderToTypeMap = [
        BooleanRuleBuilder::class => 'boolean',
        NumericRuleBuilder::class => 'numeric',
        DateRangeRuleBuilder::class => 'string',
        TimestampRangeRuleBuilder::class => 'string',
        StartsWithRuleBuilder::class => 'string',
        StringSearchRuleBuilder::class => 'string',
        MetadataRuleBuilder::class => 'object',
    ];
    public static array $filterBuilderToExampleMap = [
        BooleanRuleBuilder::class => 'true',
        NumericRuleBuilder::class => '1',
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
