<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects;

use DomainException;

class Pagination
{
    private int $page;
    private int $perPage;

    public function __construct(int $page, int $perPage)
    {
        if ($page <= 0) {
            throw new DomainException('Bad page number. Need more than zero');
        }

        if ($perPage <= 0) {
            throw new DomainException('Bad per page number. Need more than zero');
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
