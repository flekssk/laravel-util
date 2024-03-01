<?php

namespace Tests\Provisions\Http\Request;

use FKS\Enums\SearchComponent\SearchCasesEnum;
use FKS\Http\Requests\FilteringDefinitions;
use FKS\Http\Requests\RuleBuilders\StringSearchRuleBuilder;
use FKS\Http\Requests\SearchRequest;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;

class TestSearchRequest extends SearchRequest
{


    public static function getAvailableFields(): array
    {
        return [
            'user_id',
            'data_owner_id',
        ];
    }

    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        return FilteringDefinitions::create(static function (FilteringDefinitions $ruleBuilder) {
            $ruleBuilder->containsBytes('collection_id');
            $ruleBuilder->containsBytes('task_id');
            $ruleBuilder->containsBytes('task_id_nullable')->nullable();
            $ruleBuilder->numeric('pages_count');
            $ruleBuilder->containsBytes('user_id');
            $ruleBuilder->containsBytes('user_id_nullable')->nullable();
            $ruleBuilder->containsBytes('user_id_required')->required();
            $ruleBuilder->contains('int_in_array')->setType(ContainsCondition::TYPE_INTEGER_IN_ARRAY);
            $ruleBuilder->containsBytes('user_id_only_contains')->onlyContains();
            $ruleBuilder->containsBytes('user_id_only_contains_required')->required()->onlyContains();
            $ruleBuilder->startsWith('comment');
            $ruleBuilder->startsWith('name_required')->required();
            $ruleBuilder->containsInteger('type_id');
            $ruleBuilder->contains('simple_string_field');
            $ruleBuilder->numeric('numeric_field');
            $ruleBuilder->integer('integer_field');
            $ruleBuilder->boolean('bool_field');
            $ruleBuilder->dateRange('created_at_date');
            $ruleBuilder->dateRange('created_at');
            $ruleBuilder->timestampRange('updated_at_timestamp');
            $ruleBuilder->timestampRange('uploaded_at');
            $ruleBuilder->addDefinition(new StringSearchRuleBuilder('search'));
            $ruleBuilder->search(
                'search_field',
                [
                    SearchCasesEnum::CAST_TO_LOWER,
                    SearchCasesEnum::WITHOUT_SPACES,
                ],
                minChars: 1,
                maxChars: 200
            );
            $ruleBuilder->metadata();
            $ruleBuilder->string('string')->nullable();
            $ruleBuilder->date('date')->nullable();
        });
    }

    public static function getSortingDefinitions(): array
    {
        return ['field1', 'field2'];
    }
}
