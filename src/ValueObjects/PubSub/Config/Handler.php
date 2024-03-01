<?php

declare(strict_types=1);

namespace FKS\ValueObjects\PubSub\Config;

final class Handler
{
    public function __construct(
        private string $jobClass,
        private ?string $eventType,
        private ?string $event,
        private ?string $connectionName,
        private string $queueName,
        private string $subscription,
        private ?int $jobDelaySeconds,
        private array $filter
    ) {
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function getJobClass(): string
    {
        return $this->jobClass;
    }

    public function getConnectionName(): ?string
    {
        return $this->connectionName;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getSubscription(): string
    {
        return $this->subscription;
    }

    public function getJobDelaySeconds(): ?int
    {
        return $this->jobDelaySeconds;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }
}
