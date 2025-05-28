<?php

declare(strict_types=1);

namespace FKS\Api\ValueObjects;

use FKS\Api\Enrichers\RequestMiddlewareInterface;

class ApiClientConfig
{
    /**
     * @param RequestMiddlewareInterface[] $requestMiddlewares
     */
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $apiClient,
        public readonly array $requestMiddlewares,
    ) {}
}