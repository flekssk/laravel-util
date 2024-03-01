# PubSub Listener

## Responsibility

Listening, receiving, transferring received messages to suitable a job for handle in queue according to a filtering conditions.

## Motivation to use

- Centralized control point for managing of settings processing messages.
- Distributed message processing out of the box

## Settings

First of all, you need to register a service provider `\FKS\Providers\PubSubListenerServiceProvider`.


And add to console kernel schedule `\App\Console\Kernel::schedule`
```PHP
    $schedule->command('FKS-pubsub-listener:run')
        ->everyMinute()
        ->sendOutputTo('/docker.stderr');
```

## Basic usage

### First step

Define own `*Job` class base on `\FKS\Job\PubSub\AbstractPubSubMessageHandlerJob` .

```PHP
<?php

declare(strict_types=1);

namespace App\Jobs\PubSub;

use App\Services\FKSMembersService;
use FKS\Job\PubSub\AbstractPubSubMessageHandlerJob;
use FKS\ValueObjects\Id;

class HandleMergedMembersJob extends AbstractPubSubMessageHandlerJob
{
    public function handle(FKSMembersService $service)
    {
        // $this->message is \FKS\ValueObjects\PubSub\Message
        // by this you will be get message data from PubSub using ->get($key) method
        $oldMemberId = Id::create($this->message->get('removed-member-id-hex'));
        $newMemberId = Id::create($this->message->get('new-member-id-hex'));
        $service->mergeDuplicates($oldMemberId, $newMemberId);
    }
}

```

### Second step

Add settings to config `config/pubsub.php` to `listeners` key.


```PHP
<?php

return [

    ......

    'listeners' => [
        [
            'project_id' => env('PUBSUB_DATA_PIPELINE_EVENTS_PROJECT_ID'),
            'subscription' => env('PUBSUB_DATA_PIPELINE_EVENTS_SUBSCRIPTION'),
            'queue_name' => config('app.queue_datatable_export'),
            'handlers' => [
                [
                    'event_type' => 'merged_members', //optional, will be checked both `data` and `attributes` sections of PubSub message
                    //key in PubSub message can be event_type, eventType, event-type, in this config only event_type
                    'event' => 'merged_members', //optional, will be checked both `data` and `attributes` sections of PubSub message
                    'job_class' => \App\Jobs\PubSub\HandleMergedMembersJob::class,
                    'filter' => [
                        'table' => 'members' //optional, expected exact match in filters key and value (will be checked both `data` and attributes `sections` of PubSub message)
                    ]
                ],
                [
                    //this job will be called for all received messages from project_id & subscription 
                    'job_class' => \App\Jobs\PubSub\HandleAnotherJob::class,
                ],
            ]
        ],
        //next will be another listener definitions to another projects and subscriptions
        [
            'project_id' => '...',
            'subscription' => '...',
            'queue_name' => '...',
            'handlers' => [
                ...
            ]
        ]
    ]
];


```
