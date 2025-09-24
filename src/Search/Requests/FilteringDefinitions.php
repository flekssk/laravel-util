<?php

namespace FKS\Search\Requests;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use FKS\Search\Enums\SearchCasesEnum;
use FKS\Search\Requests\RuleBuilders\BooleanRuleBuilder;
use FKS\Search\Requests\RuleBuilders\ContainsOneOfEnumRuleBuilder;
use FKS\Search\Requests\RuleBuilders\ContainsRuleBuilder;
use FKS\Search\Requests\RuleBuilders\DateRangeRuleBuilder;
use FKS\Search\Requests\RuleBuilders\EqualsRuleBuilder;
use FKS\Search\Requests\RuleBuilders\MetadataRuleBuilder;
use FKS\Search\Requests\RuleBuilders\NumericRuleBuilder;
use FKS\Search\Requests\RuleBuilders\OneOfEnumRuleBuilder;
use FKS\Search\Requests\RuleBuilders\RuleBuilder;
use FKS\Search\Requests\RuleBuilders\StartsWithRuleBuilder;
use FKS\Search\Requests\RuleBuilders\StringCombinedSearchRuleBuilder;
use FKS\Search\Requests\RuleBuilders\StringSearchRuleBuilder;
use FKS\Search\Requests\RuleBuilders\TimestampRangeRuleBuilder;
use FKS\Search\ValueObjects\Conditions\ContainsCondition;
use FKS\Search\ValueObjects\Conditions\EqualsCondition;
use FKS\Search\ValueObjects\Conditions\NumericCondition;
use Iterator;

class FilteringDefinitions implements Iterator, Arrayable
{
    private array $definitions = [];
    private int $position = 0;

    public function addDefinition(RuleBuilder $ruleBuilder): self
    {
        $this->definitions[] = $ruleBuilder;
        return $this;
    }

    public function array(string $filterParam): ContainsRuleBuilder
    {
        return $this->contains($filterParam, true)->setIsArray(true);
    }

    public function contains(string $filterParam, bool $isArray = false): ContainsRuleBuilder
    {
        $ruleBuilder = new ContainsRuleBuilder($filterParam);
        $ruleBuilder->setIsArray($isArray);
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

    public function combinedSearch(
        string $filerName,
        array $searchColumns,
        SearchCasesEnum|array $case = SearchCasesEnum::CAST_TO_LOWER,
        int $minChars = 3,
        int $maxChars = 100,
    ) {
        return tap(
            new StringCombinedSearchRuleBuilder($filerName, $searchColumns, $case, $minChars, $maxChars),
            fn ($ruleBuilder) => $this->addDefinition($ruleBuilder)
        );
    }

    public function oneOfEnum(string $filterParam, string $enumClassString, ?BackedEnum $shouldBeSkipped = null): OneOfEnumRuleBuilder
    {
        return tap(new OneOfEnumRuleBuilder($enumClassString, $filterParam, $shouldBeSkipped), function ($ruleBuilder) {
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

    public static function create(Closure $closure): self
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
