<?php

declare(strict_types=1);

namespace FKS\Job\PubSub;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use FKS\ValueObjects\PubSub\Message;

abstract class AbstractPubSubMessageHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}
