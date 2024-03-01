<?php

declare(strict_types=1);

namespace Tests\Provisions\Command\PubSub;

use FKS\Job\PubSub\AbstractPubSubMessageHandlerJob;

class TestJob2 extends AbstractPubSubMessageHandlerJob
{
    public function handle()
    {

    }
}
