<?php

declare(strict_types=1);

namespace FKS\Http\Requests\RuleBuilders;

use BackedEnum;
use DomainException;
use Illuminate\Validation\Rule;
use FKS\ValueObjects\SearchConditions\Conditions\StringCondition;

class OneOfEnumRuleBuilder extends RuleBuilder implements HasEnumFilterValuesInterface
{
    /** @param class-string<BackedEnum> $enumClassString */
    public function __construct(protected string $enumClassString, string $filterParam = null, protected ?BackedEnum $shouldBeSkipped = null)
    {
        if (!is_a($enumClassString, BackedEnum::class, true)) {
            throw new DomainException('Enum param must be valid enum class string');
        }

        parent::__construct($filterParam);
    }

    public function getRules(string $filterParamName): array
    {
        $filterParam = str_replace('.', '\.', $this->getFilterParam());
        $rules = [
            'string',
            Rule::in(array_map(static fn ($enumElement) => $enumElement->value, $this->enumClassString::cases())),
        ];

        if ($this->required) {
            $rules[] = 'required';
        }

        return [
            "$filterParamName.$filterParam" => $rules,
        ];
    }

    public function getConditions($data): array
    {
        if ($data === $this->shouldBeSkipped?->value) {
            return [];
        }

        return [
            new StringCondition($this->getFilterParam(), $data, '=', 'string'),
        ];
    }

    public function getType(): ?string
    {
        return 'string';
    }

    /**
     * @return class-string<BackedEnum>
     */
    public function getEnumClassString(): string
    {
        return $this->enumClassString;
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
                "filter." . $this->getFilterParam() =>
                    'Valid ' . $this->getFilterParam() . ' values are ' . $this->makeListOfEnum($this->enumClassString),
            ],
        ];
    }

    protected function makeListOfEnum(string $enumClassString): string
    {
        /** @var BackedEnum $enumClassString */
        return implode(',', array_map(static fn($enumElement) => '"' . $enumElement->value . '"', $enumClassString::cases()));
    }
}
