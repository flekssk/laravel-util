<?php

declare(strict_types=1);

namespace FKS\ValueObjects\SearchConditions;

use FKS\Exceptions\FKSDomainException;

class Pagination
{
    private int $page;
    private int $perPage;

    public function __construct(int $page, int $perPage)
    {
        if ($page <= 0) {
            throw new FKSDomainException('Bad page number. Need more than zero');
        }

        if ($perPage <= 0) {
            throw new FKSDomainException('Bad per page number. Need more than zero');
        }

        $this->page = $page;
        $this->perPage = $perPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
