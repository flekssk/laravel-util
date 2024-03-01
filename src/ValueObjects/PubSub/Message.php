<?php

declare(strict_types=1);

namespace FKS\ValueObjects\PubSub;

use Google\Cloud\PubSub\Message as PubSubMessage;
use GuzzleHttp\Utils;
use Illuminate\Support\Collection;

final class Message
{
    private Collection $data;
    private Collection $attributes;

    public function __construct(array $data, array $attributes)
    {
        $this->data = collect($data);
        $this->attributes = collect($attributes);
    }

    public static function fromPubSubMessage(PubSubMessage $message): self
    {
        return new self(Utils::jsonDecode($message->data(), true), $message->attributes());
    }

    public function get(string $key, mixed $default = null)
    {
        return $this->data->get($key) ?? $this->attributes->get($key) ?? $default;
    }

    public function has(string $key): bool
    {
        return $this->data->has($key) || $this->attributes->has($key);
    }

    public function getMemberId(): ?string
    {
        foreach (['member_id', 'memberId', 'member-id'] as $key) {
            $memberId = $this->get($key);
            if ($memberId !== null) {
                return $memberId;
            }
        }
        return null;
    }

    public function getEvent(): ?string
    {
        return $this->get('event');
    }

    public function getEventType(): ?string
    {
        foreach (['event-type', 'event_type', 'eventType'] as $key) {
            if ($this->has($key)) {
                return $this->get($key);
            }
        }
        return null;
    }

    public function getData(): array
    {
        return $this->data->toArray();
    }

    public function getAttributes(): array
    {
        return $this->attributes->toArray();
    }
}
