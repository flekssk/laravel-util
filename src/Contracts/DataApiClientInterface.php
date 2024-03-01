<?php

declare(strict_types=1);

namespace FKS\Contracts;

interface DataApiClientInterface
{
    public function getSearchMembersIds(string $keyword, array $pcpIds, int $limit = 500): array;
}
