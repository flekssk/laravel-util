<?php

namespace FKS\Search\Requests\RuleBuilders;

use BackedEnum;
use DomainException;
use Illuminate\Validation\Rule;

class ContainsOneOfEnumRuleBuilder extends ContainsRuleBuilder implements HasSwaggerExampleInterface, HasEnumFilterValuesInterface
{
    /** @param class-string<BackedEnum> $enumClassString */
    public function __construct(protected string $enumClassString, string $filterParam = null)
    {
        if (!is_a($enumClassString, BackedEnum::class, true)) {
            throw new DomainException('Enum param must be valid enum class string');
        }

        parent::__construct($filterParam);
    }

    public function getRules(string $filterParamName): array
    {
        $rules = parent::getRules($filterParamName);

        $filterParam = str_replace('.', '\.', $this->getFilterParam());
        // Override rule for valid enum values
        $rules["$filterParamName.$filterParam.contains.*"] = $this->buildContainsRule();

        if (!$this->onlyContains) {
            $rules["$filterParamName.$filterParam.notcontains.*"] = $this->buildNotContainsRule();
        }

        return $rules;
    }

    protected function buildContainsRule(): string
    {
        return $this->buildTypeRules() . $this->buildNullable() . $this->buildRequired() . $this->buildInCondition();
    }

    protected function buildNotContainsRule(): string
    {
        return $this->buildTypeRules() . $this->buildNullable() . $this->buildInCondition();
    }

    protected function buildNullable(): string
    {
        return $this->nullable ? '|nullable' : '';
    }

    protected function buildRequired(): string
    {
        return $this->required && $this->onlyContains ? "|required" : '';
    }

    protected function buildInCondition(): string
    {
        return "|" . Rule::in(array_map(static fn($enumElement) => $enumElement->value, $this->enumClassString::cases()));
    }

    protected function buildTypeRules(): string
    {
        return match($this->type) {
            'string' => 'string',
            'integer', 'integer_in_array', 'int' => 'integer',
            'uuid_or_hex', 'bytes_in_array' => 'uuid_or_hex',
        };
    }

    public function getExample(): ?string
    {
        return $this->enumClassString::cases()[0]->value;
    }

    public function getEnumValues(): array
    {
        return array_map(static fn($enumElement) => $enumElement->value, $this->enumClassString::cases());
    }

    public function buildMessages(): array
    {
        if (!is_a($this->enumClassString, BackedEnum::class, true)) {
            throw new DomainException('Enum param must be valid enum class string');
        }

        return [
            [
                "filter.$this->enumClassString.contains.*" =>
                    $this->getFilterParam() . ' filter values can be on of ' . $this->makeListOfEnum($this->enumClassString),
            ],
            [
                "filter." . $this->getFilterParam() . ".notcontains.*" =>
                    $this->getFilterParam() . ' filter values can be on of ' . $this->makeListOfEnum($this->enumClassString),
            ],
        ];
    }

    protected function makeListOfEnum(string $enumClassString): string
    {
        /** @var BackedEnum $enumClassString */
        return implode(',', array_map(static fn($enumElement) => '"' . $enumElement->value . '"', $enumClassString::cases()));
    }
}
