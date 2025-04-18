<?php

namespace FKS\Http\Requests;

use FKS\Contracts\PaginatorInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use FKS\Collections\SearchConditionsCollection;
use FKS\Http\Requests\RuleBuilders\RuleBuilder;
use FKS\Http\Requests\SortingRuleBuilders\Enums\SortAsEnum;
use FKS\ValueObjects\SearchConditions\SearchConditions;
use Illuminate\Support\Collection;

abstract class SearchRequest extends FormRequest
{
    public const DEFAULT_PER_PAGE = 20;
    public const MAX_PER_PAGE = 200;

    public static function getAdditionalFilterSwaggerProperties(): array
    {
        return [];
    }

    public function rules(): array
    {
        $paginatorInstance = app(PaginatorInterface::class);
        $rules = [
            'available_fields' => [
                static::getAvailableFields() !== [] ? 'required' : 'nullable',
                'array'
            ],
            'available_fields.*' => [
                static::getAvailableFields() !== [] ? 'required' : 'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, static::getAvailableFields(), true)) {
                        $message = "The selected available_field '$value' is invalid. Valid available_fields are: "
                            . implode(
                                ', ',
                                static::getAvailableFields()
                            );
                        return $fail($message);
                    }
                },
            ],

        ];
        return array_merge(
            $rules,
            $this->sortRules(),
            $this->filteringRules(),
            $this->additionalRules(),
            $paginatorInstance::getPaginatorValidatorRules(static::getPerPageMax()),
        );
    }

    public function sortRules(): array
    {
        return [
            'sort' => ['array'],
            'sort.*.field' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    [$valuePrefix] = explode('.', $value);
                    if (
                        !in_array($value, static::getSortingDefinitions(), true)
                        && !in_array($valuePrefix, static::getSortingDefinitions(), true)
                    ) {
                        $message = "The selected sort '$value' is invalid. Sortable fields are: "
                            . implode(
                                ', ',
                                static::getSortingDefinitions()
                            );
                        return $fail($message);
                    }
                },
            ],
            'sort.*.direction' => [
                'required',
                'string',
                Rule::in(['asc', 'desc']),
            ],
            'sort.*.sort_as' => [
                'string',
                Rule::enum(SortAsEnum::class),
            ],
        ];
    }

    public function filteringRules(): array
    {
        $rules = [];
        foreach (static::getFilteringDefinitions() as $ruleBuilder) {
            /** @var $ruleBuilder RuleBuilder */
            $rules += $ruleBuilder->getRules();
        }
        return $rules;
    }

    public function additionalRules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            $this->getFilteringRulesMessages()
        );
    }

    public function getFilteringRulesMessages(): array
    {
        $messages = [];
        foreach (static::getFilteringDefinitions() as $ruleBuilder) {
            /** @var $ruleBuilder RuleBuilder */
            if (method_exists($ruleBuilder, 'buildMessages')) {
                $messages += $ruleBuilder->buildMessages();
            }
        }
        return $messages;
    }

    public function getSearchConditions(): SearchConditions
    {
        $filter = new SearchConditionsCollection();
        $paginatorInstance = app(PaginatorInterface::class);
        $requestFilters = Arr::get($this->all(), 'filter', []);

        /** @var RuleBuilder $builder */
        foreach (static::getFilteringDefinitions() as $builder) {
            if (!$builder->isCustomizedBuilder() && $builder->getFilterParam()) {
                $filterExists = array_key_exists($builder->getFilterParam(), $requestFilters);
                $data = $requestFilters[$builder->getFilterParam()] ?? null;

                if (!$filterExists || (is_null($data) && !$builder->isNullable())) {
                    continue;
                }
                foreach ($builder->getConditions($data) as $condition) {
                    $filter->push($condition);
                }
            } else {
                foreach ($builder->getConditions($this->all()) as $condition) {
                    $filter->push($condition);
                }
            }
        }

        $additionalParamNames = array_unique(array_map(
            static fn($item) => explode('.', $item)[0] ?? null,
            array_keys($this->additionalRules())
        ));
        $additionalParams = [];
        foreach ($additionalParamNames as $paramName) {
            $value = $this->get($paramName);
            if ($value === null) {
                continue;
            }
            $additionalParams[$paramName] = $value;
        }

        return new SearchConditions(
            $this->get('available_fields', []),
            $filter,
            new Collection($additionalParams),
            (bool)$this->get('only_counter', false),
            new Collection($this->get('sort')),
            $paginatorInstance->setupFromRequest($this),
        );
    }

    public static function getPerPageMax(): int
    {
        return self::MAX_PER_PAGE;
    }

    public static function getDefaultPerPage(): int
    {
        return self::DEFAULT_PER_PAGE;
    }

    abstract public static function getAvailableFields(): array;

    abstract public static function getFilteringDefinitions(): FilteringDefinitions;

    abstract public static function getSortingDefinitions(): array;
}
