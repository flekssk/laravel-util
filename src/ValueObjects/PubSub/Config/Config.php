<?php

namespace FKS\ValueObjects\PubSub\Config;

use Closure;
use Illuminate\Support\Collection;
use FKS\Job\PubSub\AbstractPubSubMessageHandlerJob;

final class Config extends Collection
{
    public static function create(array $config, Closure $errorLogCallback): self
    {
        $config = array_map(function ($listener) use ($errorLogCallback) {
            $listenerLog = static fn($message)
                => $errorLogCallback("Can't configure listener. $message. Details: " . json_encode($listener));
            $projectId = $listener['project_id'] ?? null;
            $subscription = $listener['subscription'] ?? null;

            if ($projectId === null) {
                $listenerLog('Required key project_id not set');
                return null;
            }
            if ($subscription === null) {
                $listenerLog('Required key subscription not set');
                return null;
            }
            $listenerConnectionName = $listener['connection_name'] ?? null;
            $listenerQueueName = $listener['queue_name'] ?? null;
            $handlers = $listener['handlers'] ?? [];
            if (count($handlers) === 0) {
                $listenerLog('Handlers not found');
                return null;
            }

            $newHandlers = [];
            foreach ($handlers as $handler) {
                $handlerLog = static fn($message)
                    => $errorLogCallback("Can't configure handler. $message. Details: " . json_encode($handler));

                $queueName = $handler['queue_name'] ?? $listenerQueueName;
                $connectionName = $handler['connection_name'] ?? $listenerConnectionName;
                if ($queueName === null) {
                    $handlerLog('Required key queue_name not set');
                    continue;
                }
                $eventType = $handler['event_type'] ?? null;
                $event = $handler['event'] ?? null;
                $jobClass = $handler['job_class'] ?? null;
                $jobDelaySeconds = $handler['job_delay_seconds'] ?? null;
                if ($jobClass === null) {
                    $handlerLog('Required key job_class not set');
                    continue;
                }

                if (!is_subclass_of($jobClass, AbstractPubSubMessageHandlerJob::class)) {
                    $handlerLog('Class contains in key job_class must be inherited of ' . AbstractPubSubMessageHandlerJob::class);
                    continue;
                }

                $filter = $handler['filter'] ?? [];
                if (!is_array($filter)) {
                    $handlerLog('Bad filter. Array required!');
                    continue;
                }

                $newHandlers[] = new Handler(
                    $jobClass,
                    $eventType,
                    $event,
                    $connectionName,
                    $queueName,
                    $subscription,
                    $jobDelaySeconds,
                    $filter
                );
            }
            if (count($newHandlers) === 0) {
                return null;
            }
            return new Listener(
                $projectId,
                $subscription,
                $newHandlers
            );
        }, $config);

        return new self(array_filter($config));
    }
}
