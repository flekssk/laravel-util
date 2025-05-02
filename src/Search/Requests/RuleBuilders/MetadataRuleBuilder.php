<?php

declare(strict_types=1);

namespace FKS\Search\Requests\RuleBuilders;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use FKS\Metadata\Enums\MetadataFilterTypeEnum;
use FKS\Search\Enums\SearchCasesEnum;
use FKS\Search\ValueObjects\Conditions\Collections\MetadataConditionsCollection;
use FKS\Search\ValueObjects\Conditions\ContainsCondition;
use FKS\Search\ValueObjects\Conditions\MetadataCondition;
use FKS\Search\ValueObjects\Conditions\NumericCondition;

class MetadataRuleBuilder extends RuleBuilder
{
    public function getRules(string $filterParamName): array
    {
        $filterParam = $this->getFilterParam();
        return [
            "$filterParamName.$filterParam" => 'array',
            "$filterParamName.$filterParam.*.metadata_key" => 'required|string',
            "$filterParamName.$filterParam.*.filter_type" => [
                'required',
                Rule::in(MetadataFilterTypeEnum::values()),
            ],
            "$filterParamName.$filterParam.*.data" => [
                'required',
                function ($attribute, $value, $fail, Validator $validator) use ($filterParam, $filterParamName) {
                    $ruleBuilder = $this->buildSubRuleBuilder(
                        MetadataFilterTypeEnum::tryFrom(Arr::get($validator->validated(), str_replace('.data', '.filter_type', $attribute)))
                    );
                    if ($ruleBuilder !== null) {
                        $data = [
                            'filter' => [
                                $filterParam => [
                                    'data' => $value
                                ]
                            ]
                        ];
                        $subValidator = \Illuminate\Support\Facades\Validator::make(
                            $data,
                            $ruleBuilder->getRules($filterParamName),
                        );
                        $validator->errors()->merge($subValidator->errors());
                    }
                }
            ]
        ];
    }

    public function getConditions($data): array
    {
        $conditions = new MetadataConditionsCollection();
        foreach ($data as $datum) {
            $subRuleBuilder = $this->buildSubRuleBuilder(MetadataFilterTypeEnum::tryFrom($datum['filter_type']));
            if ($subRuleBuilder) {
                $conditions->add($datum['metadata_key'], $subRuleBuilder->getConditions($datum['data']));
            }
        }

        return [new MetadataCondition($conditions,  $this->filterParam)];
    }

    public function buildSubRuleBuilder(?MetadataFilterTypeEnum $type): ?RuleBuilder
    {
        $ruleBuilder = null;

        if ($type !== null) {
            if ($type->isContains()) {
                $ruleBuilder = (new ContainsRuleBuilder("{$this->getFilterParam()}.data", false))
                    ->setType(
                        match ($type) {
                            MetadataFilterTypeEnum::TYPE_CONTAINS_HEX => ContainsCondition::TYPE_HEX_STRING,
                            MetadataFilterTypeEnum::TYPE_CONTAINS_STRING => ContainsCondition::TYPE_STRING,
                            default => throw new Exception("Undefined metadata filter type $type?->value")
                        }
                    )
                    ->onlyContains();
            } else if ($type === MetadataFilterTypeEnum::TYPE_SEARCH_STRING) {
                $ruleBuilder = new StringSearchRuleBuilder("{$this->getFilterParam()}.data", SearchCasesEnum::CAST_TO_LOWER);
            } else {
                $ruleBuilder = new NumericRuleBuilder("{$this->getFilterParam()}.data", false);
                $ruleBuilder->setType(NumericCondition::TYPE_INTEGER_STRING);
            }
        }

        return $ruleBuilder;
    }
}
