<?php

declare(strict_types=1);

namespace FKS\Services\PubSub;

use Closure;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use FKS\Contracts\PubSub\ClientManagerInterface;
use FKS\Facades\FKSLogger;
use FKS\Job\PubSub\AbstractPubSubMessageHandlerJob;
use FKS\ValueObjects\PubSub\Config\Config;
use FKS\ValueObjects\PubSub\Config\Handler;
use FKS\ValueObjects\PubSub\Config\Listener;
use FKS\ValueObjects\PubSub\Message;
use Throwable;

class ListenerService
{
    private const BATCH_SIZE = 50;

    private ClientManagerInterface $clientManager;
    private QueueingDispatcher $dispatcher;
    private Config $config;
    private ?Closure $errorLogCallback = null;

    public function __construct(
        ClientManagerInterface $clientManager,
        QueueingDispatcher $dispatcher,
        Config $config
    ) {
        $this->clientManager = $clientManager;
        $this->config = $config;
        $this->dispatcher = $dispatcher;
    }

    public function setErrorLogCallback(Closure $errorLogCallback): self
    {
        $this->errorLogCallback = $errorLogCallback;
        return $this;
    }

    public function run(): void
    {
        foreach ($this->config as $listener) {
            try {
                $this->handleListener($listener);
            } catch (Throwable $exception) {
                $this->logException($exception);
            }
        }
    }

    private function handleListener(Listener $listener): void
    {
        $pubSubMessages = $this->clientManager->getMessages($listener->getProjectId(), $listener->getSubscription());

        foreach (array_chunk($pubSubMessages, self::BATCH_SIZE) as $chunkedPubSubMessages) {
            foreach ($chunkedPubSubMessages as $pubSubMessage) {
                foreach ($listener->getHandlers() as $handler) {
                    try {
                        $message = Message::fromPubSubMessage($pubSubMessage);
                        $this->handleMessage($handler, $message);
                    } catch (Throwable $exception) {
                        $this->logException($exception);
                    }
                }
            }
            $this->clientManager->acknowledgeBatch(
                $listener->getProjectId(),
                $listener->getSubscription(),
                $pubSubMessages
            );
        }
    }

    private function handleMessage(Handler $handler, Message $message): void
    {
        if (!$this->isSuitableHandler($handler, $message)) {
            return;
        }
        $jobClass = $handler->getJobClass();
        /** @var AbstractPubSubMessageHandlerJob $job */
        $job = new $jobClass($message);
        if ($handler->getConnectionName()) {
            $job->onConnection($handler->getConnectionName());
        }
        if ($handler->getJobDelaySeconds() !== null) {
            $job->delay(Carbon::now()->addSeconds($handler->getJobDelaySeconds()));
        }

        $job->onQueue($handler->getQueueName());

        FKSLogger::info(
            sprintf("PubSub listener service handle %s event", $message->getEvent()),
            [
                'data' => $message->getData(),
                'attributes' => $message->getAttributes()
            ],
            [
                'action_type' => 'pubsub',
                'action' => 'pubsub.listen',
                'member_id' => $message->getMemberId(),
                'subscription' => $handler->getSubscription()
            ]
        );

        $this->dispatcher->dispatchToQueue($job);
    }

    public function isSuitableHandler(Handler $handler, Message $message): bool
    {
        if (!$this->isPassedFilter($handler, $message)) {
            return false;
        }
        if ($handler->getEvent() === null && $handler->getEventType() === null) {
            return true;
        }
        if ($handler->getEventType() !== $message->getEventType()) {
            return false;
        }
        if ($handler->getEvent() !== null && $handler->getEvent() !== $message->getEvent()) {
            return false;
        }
        return true;
    }

    public function isPassedFilter(Handler $handler, Message $message): bool
    {
        foreach ($handler->getFilter() as $key => $filterValue) {
            $value = Arr::get($message->getData(), $key, Arr::get($message->getAttributes(), $key));
            if ($value !== $filterValue) {
                return false;
            }
        }
        return true;
    }

    private function logException(Throwable $exception): void
    {
        $callback = $this->errorLogCallback;
        if ($callback) {
            $callback($exception);
        }
    }
}
