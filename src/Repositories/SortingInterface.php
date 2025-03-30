<?php

declare(strict_types=1);

namespace FKS\Repositories;

interface SortingInterface
{
    public function getSorts(): array;
    public function getExamples(): array;
}
