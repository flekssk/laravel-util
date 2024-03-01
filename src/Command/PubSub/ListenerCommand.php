<?php

declare(strict_types=1);

namespace FKS\Command\PubSub;

use Illuminate\Console\Command;
use FKS\Services\PubSub\ListenerService;

class ListenerCommand extends Command
{
    protected $signature = 'FKS-pubsub-listener:run';
    protected $description = 'Listening and handling PubSub messages';

    public function handle(ListenerService $service): void
    {
        $service->run();
    }
}
