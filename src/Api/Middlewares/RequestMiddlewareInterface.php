<?php

declare(strict_types=1);

namespace FKS\Api\Middlewares;

interface RequestMiddlewareInterface
{
    public function handle(string $method, string $uri, array $options): array;
}