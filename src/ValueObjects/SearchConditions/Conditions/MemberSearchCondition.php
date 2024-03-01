<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions\Conditions;

class MemberSearchCondition extends Condition
{
    protected ?string $filterParam = 'member_id';

    public function __construct(
        private string $memberSearch,
        private array $containsPcpIds
    ) {}

    public function getMemberSearch(): string
    {
        return $this->memberSearch;
    }

    public function getContainsPcpIds(): array
    {
        return $this->containsPcpIds;
    }
}
