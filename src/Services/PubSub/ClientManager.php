<?php

declare(strict_types=1);

namespace FKS\Services\PubSub;

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use FKS\Contracts\PubSub\ClientManagerInterface;


final class ClientManager implements ClientManagerInterface
{
    private array $clients = [];

    private function getClient($projectId): PubSubClient
    {
        $client = $this->clients[$projectId] ?? null;
        if ($client === null) {
            $this->clients[$projectId] = $client = new PubSubClient(['projectId' => $projectId]);
        }
        return $client;
    }

    private function getSubscription(string $projectId, string $subscription): Subscription
    {
        $client = $this->getClient($projectId);
        return $client->subscription($subscription);
    }

    public function getMessages(string $projectId, string $subscription): array
    {
        $subscription = $this->getSubscription($projectId, $subscription);
        return $subscription->pull(['returnImmediately' => true]);
    }

    public function acknowledgeBatch(string $projectId, string $subscription, array $messages): void
    {
        if (count($messages) === 0) {
            return;
        }
        $subscription = $this->getSubscription($projectId, $subscription);
        $subscription->acknowledgeBatch($messages);
    }
}
