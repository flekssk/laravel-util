<?php

declare(strict_types=1);

namespace FKS\Contracts\PubSub;

interface ClientManagerInterface
{
    public function getMessages(string $projectId, string $subscription): array;
    public function acknowledgeBatch(string $projectId, string $subscription, array $messages): void;
}
