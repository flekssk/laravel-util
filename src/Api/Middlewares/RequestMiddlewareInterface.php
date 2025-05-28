<?php

declare(strict_types=1);

namespace FKS\Api\Enrichers;

interface RequestMiddlewareInterface
{
    public function handle(string $method, string $uri, array $options): array;
}