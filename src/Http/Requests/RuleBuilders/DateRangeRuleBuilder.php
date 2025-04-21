<?php

namespace FKS\Http\Requests\RuleBuilders;

use Carbon\Carbon;
use FKS\ValueObjects\SearchConditions\Conditions\Condition;
use FKS\ValueObjects\SearchConditions\Conditions\DateRangeCondition;
use FKS\ValueObjects\SearchConditions\Conditions\EmptyOrNullCondition;

class DateRangeRuleBuilder extends RuleBuilder
{
    protected ?string $type = DateRangeCondition::TYPE_DATE;
    protected const FORMAT = 'Y-m-d';

    public function getRules(string $filterParamName): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());
        $format = static::FORMAT;

        $rules = [
            "$filterParamName.$filterParam" => [
                'array',
                function ($param, $value, $fail) {
                    if ($value === []) {
                        $fail("The $param.from and $param.to is required when $param is passed.");
                    }
                }
            ],
            "$filterParamName.$filterParam.from" => ($this->nullable ? "present_with:filter.$filterParam" : "date_format:$format|required_with:filter.$filterParam|before_or_equal:filter.$filterParam.to|after:1900-01-01|before:2100-01-01"),
            "$filterParamName.$filterParam.to" => ($this->nullable ? "present_with:filter.$filterParam" : "date_format:$format|required_with:filter.$filterParam|after_or_equal:filter.$filterParam.from|after:1900-01-01|before:2100-01-01"),
        ];

        if ($this->required) {
            $rules["$filterParamName.$filterParam"][] = 'required';
        }

        return $rules;
    }

    /**
     * @param $data
     * @return Condition[]
     */
    public function getConditions($data): array
    {
        if ($this->isNullable() && is_null($data['from']) && is_null($data['to'])) {
            return [new EmptyOrNullCondition($this->getFilterParam())];
        } else {
            $from = Carbon::createFromFormat(static::FORMAT, $data['from'], 'UTC');
            $to = Carbon::createFromFormat(static::FORMAT, $data['to'], 'UTC');

            return [
                new DateRangeCondition($this->getFilterParam(), $from, $to, $this->type)
            ];
        }

    }
}
