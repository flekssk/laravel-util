<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

class MemberSearchCondition extends Condition
{
    protected ?string $filterParam = 'member_id';

    public function __construct(
        private readonly string $memberSearch,
        private readonly array $containsPcpIds
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
