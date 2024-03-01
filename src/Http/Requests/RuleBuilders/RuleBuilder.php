<?php

namespace FKS\Http\Requests\RuleBuilders;

abstract class RuleBuilder implements RuleBuilderInterface
{
    protected ?string $type = null;
    protected bool $nullable = false;
    protected bool $required = false;
    protected bool $deprecated = false;


    public function  __construct(protected ?string $filterParam = null, protected bool $escapeDotInParam = true)
    {
    }

    public function getFilterParam(): ?string
    {
        return $this->filterParam;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getSwaggerType(): ?string
    {
        return $this->getType();
    }

    public function setNullable(bool $value): self
    {
        $this->nullable = $value;
        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function nullable(): self
    {
        $this->setNullable(true);
        return $this;
    }

    public function setRequired(bool $value): self
    {
        $this->required = $value;
        return $this;
    }

    public function required(): self
    {
        return $this->setRequired(true);
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isCustomizedBuilder(): bool
    {
        return false;
    }

    public function setDeprecated(): self
    {
        $this->deprecated = true;

        return $this;
    }

    public function deprecated(): self
    {
        return $this->setDeprecated();
    }

    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }
}
