<?php

declare(strict_types=1);

namespace FKS\ValueObjects\PubSub\Config;

final class Listener
{
    private string $projectId;
    private string $subscription;
    private array $handlers;

    public function __construct(string $projectId, string $subscription, array $handlers)
    {
        $this->projectId = $projectId;
        $this->subscription = $subscription;
        $this->handlers = $handlers;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getSubscription(): string
    {
        return $this->subscription;
    }

    /**
     * @return Handler[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
