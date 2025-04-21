<?php

namespace FKS\Http\Requests;

use Illuminate\Contracts\Support\Arrayable;
use FKS\Enums\SearchComponent\SearchCasesEnum;
use FKS\Http\Requests\RuleBuilders\BooleanRuleBuilder;
use FKS\Http\Requests\RuleBuilders\ContainsOneOfEnumRuleBuilder;
use FKS\Http\Requests\RuleBuilders\ContainsRuleBuilder;
use FKS\Http\Requests\RuleBuilders\DateRangeRuleBuilder;
use FKS\Http\Requests\RuleBuilders\EqualsRuleBuilder;
use FKS\Http\Requests\RuleBuilders\MetadataRuleBuilder;
use FKS\Http\Requests\RuleBuilders\NumericRuleBuilder;
use FKS\Http\Requests\RuleBuilders\OneOfEnumRuleBuilder;
use FKS\Http\Requests\RuleBuilders\RuleBuilder;
use FKS\Http\Requests\RuleBuilders\StartsWithRuleBuilder;
use FKS\Http\Requests\RuleBuilders\StringSearchRuleBuilder;
use FKS\Http\Requests\RuleBuilders\TimestampRangeRuleBuilder;
use FKS\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\EqualsCondition;
use FKS\ValueObjects\SearchConditions\Conditions\NumericCondition;

class FilteringDefinitions implements \Iterator, Arrayable
{
    private array $definitions = [];
    private int $position = 0;

    public function addDefinition(RuleBuilder $ruleBuilder): self
    {
        $this->definitions[] = $ruleBuilder;
        return $this;
    }

    public function contains(string $filterParam): ContainsRuleBuilder
    {
        $ruleBuilder = new ContainsRuleBuilder($filterParam);
        $this->addDefinition($ruleBuilder);
        return $ruleBuilder;
    }

    public function startsWith(string $filterParam): StartsWithRuleBuilder
    {
        $ruleBuilder = new StartsWithRuleBuilder($filterParam);
        $this->addDefinition($ruleBuilder);
        return $ruleBuilder;
    }

    public function containsBytes(string $filterParam): ContainsRuleBuilder
    {
        return $this->contains($filterParam)->setType(ContainsCondition::TYPE_BYTES);
    }

    public function containsInteger(string $filterParam): ContainsRuleBuilder
    {
        return $this->contains($filterParam)->setType(ContainsCondition::TYPE_INTEGER);
    }

    public function containsBoolean(string $filterParam): ContainsRuleBuilder
    {
        return $this->contains($filterParam)->setType(ContainsCondition::TYPE_BOOLEAN);
    }

    public function numeric(string $filterParam): NumericRuleBuilder
    {
        $ruleBuilder = new NumericRuleBuilder($filterParam);
        $this->addDefinition($ruleBuilder);
        return $ruleBuilder;
    }

    public function integer(string $filterParam): NumericRuleBuilder
    {
        return $this->numeric($filterParam)->setType(NumericCondition::TYPE_INTEGER);
    }

    public function boolean(string $filterParam): BooleanRuleBuilder
    {
        $ruleBuilder = new BooleanRuleBuilder($filterParam);
        $this->addDefinition($ruleBuilder);
        return $ruleBuilder;
    }

    public function dateRange(string $filterParam): DateRangeRuleBuilder
    {
        $ruleBuilder = new DateRangeRuleBuilder($filterParam);
        $this->addDefinition($ruleBuilder);
        return $ruleBuilder;
    }

    public function timestampRange(string $filterParam): TimestampRangeRuleBuilder
    {
        $ruleBuilder = new TimestampRangeRuleBuilder($filterParam);
        $this->addDefinition($ruleBuilder);
        return $ruleBuilder;
    }

    public function metadata(string $filterParam = 'metadata'): MetadataRuleBuilder
    {
        $ruleBuilder = new MetadataRuleBuilder($filterParam);
        $this->addDefinition($ruleBuilder);
        return $ruleBuilder;
    }

    /**
     * @param SearchCasesEnum|SearchCasesEnum[] $case
     */
    public function search(
        string $field,
        SearchCasesEnum|array $case = SearchCasesEnum::CAST_TO_LOWER,
        int $minChars = 3,
        int $maxChars = 100,
    ): StringSearchRuleBuilder {
        return tap(new StringSearchRuleBuilder($field, $case, $minChars, $maxChars), function ($ruleBuilder) {
            $this->addDefinition($ruleBuilder);
        });
    }

    public function oneOfEnum(string $filterParam, string $enumClassString): OneOfEnumRuleBuilder
    {
        return tap(new OneOfEnumRuleBuilder($enumClassString, $filterParam), function ($ruleBuilder) {
            $this->addDefinition($ruleBuilder);
        });
    }

    public function containsOneOfEnum(string $filterParam, string $enumClassString): ContainsOneOfEnumRuleBuilder
    {
        return tap(new ContainsOneOfEnumRuleBuilder($enumClassString, $filterParam), function ($ruleBuilder) {
            $this->addDefinition($ruleBuilder);
        });
    }

    public function string(string $filterParam): EqualsRuleBuilder
    {
        return tap(new EqualsRuleBuilder($filterParam, type: EqualsCondition::STRING_TYPE), function ($ruleBuilder) {
            $this->addDefinition($ruleBuilder);
        });
    }

    public function date(string $filterParam): EqualsRuleBuilder
    {
        return tap(new EqualsRuleBuilder($filterParam, type: EqualsCondition::DATE_TYPE), function ($ruleBuilder) {
            $this->addDefinition($ruleBuilder);
        });
    }

    public static function create(\Closure $closure): self
    {
        $filteringDefinitions = new self();
        $closure($filteringDefinitions);
        return $filteringDefinitions;
    }

    public function current(): mixed
    {
        return $this->definitions[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->definitions[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function toArray(): array
    {
        return $this->definitions;
    }
}
