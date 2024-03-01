<?php

namespace FKS\Http\Requests\RuleBuilders;

use Illuminate\Support\Arr;
use FKS\ValueObjects\SearchConditions\Conditions\MemberSearchCondition;

class MemberSearchRuleBuilder extends RuleBuilder
{
    public function getRules(): array
    {
        return [
            'member_search' => 'nullable|min:3|max:100' . ($this->required ? "|required" : ''),
        ];
    }

    /**
     * @param $data
     * @return array|\FKS\ValueObjects\SearchConditions\Conditions\Condition[]
     */
    public function getConditions($data): array
    {
        $memberSearch = Arr::get($data, 'member_search');
        $containsPcpIds = Arr::get($data, 'filter.pcp_id.contains', []);
        if ($memberSearch) {
            return [new MemberSearchCondition($memberSearch, $containsPcpIds)];
        }
        return [];
    }

    public function isCustomizedBuilder(): bool
    {
        return true;
    }
}
