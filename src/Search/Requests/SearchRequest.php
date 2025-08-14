<?php

namespace FKS\Search\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use FKS\Search\Collections\SearchConditionsCollection;
use FKS\Search\Contracts\PaginatorInterface;
use FKS\Search\Enums\SortParamSchemaEnum;
use FKS\Search\Helpers\SearchComponentConfigHelper;
use FKS\Search\Requests\RuleBuilders\RuleBuilder;
use FKS\Search\Requests\SortingRuleBuilders\Enums\SortAsEnum;
use FKS\Search\ValueObjects\SearchConditions;

abstract class SearchRequest extends FormRequest
{
    public const DEFAULT_PER_PAGE = 20;
    public const MAX_PER_PAGE = 200;

    abstract public static function getAvailableFields(): array;
    abstract public static function getSortingDefinitions(): array;

    public static function getAdditionalFilterSwaggerProperties(): array
    {
        return [];
    }

    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        return new FilteringDefinitions();
    }

    public static function getSettingsDefinitions(): SettingsDefinitions
    {
        return new SettingsDefinitions();
    }

    public static function getPaginationInstance(): PaginatorInterface
    {
        return app(PaginatorInterface::class);
    }

    public function rules(): array
    {
        $availableFieldsParamName = SearchComponentConfigHelper::getConfig()->availableFieldsParamName;
        $paginatorInstance = static::getPaginationInstance();
        $rules = [
            $availableFieldsParamName => [
                'array',
            ],
            "$availableFieldsParamName.*" => [
                'string',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, static::getAvailableFields(), true)) {
                        $message = "The selected available_field '$value' is invalid. Valid available_fields are: "
                            . implode(
                                ', ',
                                static::getAvailableFields(),
                            );
                        return $fail($message);
                    }

                    return true;
                },
            ],
        ];
        return array_merge(
            $rules,
            $this->sortRules(),
            $this->filteringRules(),
            $this->additionalRules(),
            self::getSettingsDefinitions()->validationRules(),
            $paginatorInstance::getPaginatorValidatorRules(static::getPerPageMax()),
        );
    }

    public function sortRules(): array
    {
        $sortParamName = SearchComponentConfigHelper::getConfig()->sortParamName;
        $rules = [
            $sortParamName => ['array'],
        ];

        switch (config('search.sort_param_schema', SortParamSchemaEnum::OBJECT)) {
            case SortParamSchemaEnum::OBJECT:
                $rules = array_merge(
                    $rules,
                    [
                        "$sortParamName.*.field" => [
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
                                            static::getSortingDefinitions(),
                                        );
                                    return $fail($message);
                                }
                                return true;
                            },
                        ],
                        "$sortParamName.*.direction" => [
                            'required',
                            'string',
                            Rule::in(['asc', 'desc']),
                        ],
                        "$sortParamName.*.sort_as" => [
                            'string',
                            Rule::enum(SortAsEnum::class),
                        ],
                    ],
                );
                break;
            case SortParamSchemaEnum::KEY_VALUE:
                $rules = array_merge(
                    $rules,
                    [
                        "$sortParamName.*" => function ($column, $direction, $fail) {
                            [, $column] = explode('.', $column);

                            if (!in_array($column, static::getSortingDefinitions(), true)) {
                                $message = "The selected sort '$column' is invalid. Sortable fields are: "
                                    . implode(
                                        ', ',
                                        static::getSortingDefinitions(),
                                    );
                                return $fail($message);
                            }
                            if (!in_array(strtolower($direction), ['asc', 'desc'])) {
                                return $fail('The selected sort direction is invalid. Valid directions are: asc, desc');
                            }
                            return true;
                        },
                    ],
                );
                break;
        }

        return $rules;
    }

    public function filteringRules(): array
    {
        $rules = [];
        foreach (static::getFilteringDefinitions() as $ruleBuilder) {
            /** @var $ruleBuilder RuleBuilder */
            $rules += $ruleBuilder->getRules(SearchComponentConfigHelper::getConfig()->filterParamName);
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
            $this->getFilteringRulesMessages(),
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
        $paginatorInstance = static::getPaginationInstance();
        $requestFilters = Arr::get($this->all(), SearchComponentConfigHelper::getConfig()->filterParamName, []);

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
            array_keys($this->additionalRules()),
        ));

        $sorts = collect($this->get(SearchComponentConfigHelper::getConfig()->sortParamName, []));

        if (SearchComponentConfigHelper::getConfig()->sortParamSchema === SortParamSchemaEnum::KEY_VALUE) {
            $sorts = $sorts->mapWithKeys(
                static function ($value, $key) {
                    return [
                        [
                            'field' => $key,
                            'direction' => strtolower($value),
                        ]
                    ];
                }
            );
        }

        return new SearchConditions(
            $this->get(SearchComponentConfigHelper::getConfig()->availableFieldsParamName, $this->getAvailableFields()),
            $filter,
            $sorts,
            $paginatorInstance->setupFromRequest($this),
            static::getSettingsDefinitions()
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
}
