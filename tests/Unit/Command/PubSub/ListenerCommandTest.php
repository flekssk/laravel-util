<?php

namespace Tests\Unit\Command\PubSub;

use Google\Cloud\PubSub\Message;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use FKS\Contracts\PubSub\ClientManagerInterface;
use FKS\Facades\Debugger;
use FKS\Facades\FKSLogger;
use FKS\Job\PubSub\AbstractPubSubMessageHandlerJob;
use FKS\Services\PubSub\ListenerService;
use FKS\ValueObjects\PubSub\Config\Config;
use Tests\Provisions\Command\PubSub\TestJob;
use Tests\Provisions\Command\PubSub\TestJob2;

class ListenerCommandTest extends TestCase
{
    public function testRun()
    {
        FKSLogger::spy();
        $config = [
            [
                'project_id' => 'p1', //required
                'subscription' => 's1', //required
                'queue_name' => 'list_qn', //required
                'connection_name' => 'list_con1', //optional
                'handlers' => [
                    [
                        'event_type' => 'merged_members', //optional
                        'event' => 'merged_members', //optional
                        'job_class' => TestJob::class, //required
                        'queue_name' => 'qn1', //optional
                        'connection_name' => 'con1', //optional
                    ],
                    [
                        'event_type' => 'merged_members', //optional
                        'event' => 'merged_members', //optional
                        'job_class' => TestJob2::class, //required
                        'queue_name' => 'qn2', //optional,
                        'connection_name' => 'con2', //optional
                    ],
                    [
                        'event_type' => 'merged_members', //optional
                        'event' => 'merged_members', //optional
                        'job_class' => TestJob2::class, //required
                    ],
                    [
                        'event_type' => 'any_event_type', //optional
                        'job_class' => TestJob2::class, //required
                    ],
                    [
                        'job_class' => TestJob2::class, //required
                    ],
                    [
                        'job_class' => TestJob2::class, //required
                        'filter' => [
                            'test_param' => 'param_value',
                        ]
                    ],
                    [
                        'job_class' => TestJob2::class, //required
                        'filter' => [
                            'test_param.sub_test_param' => 'sub_test_value',
                        ]
                    ],
                ]
            ]
        ];
        $errors = [];
        $errorLogCallback = static function ($message) use (&$errors) {
            $errors[] = $message;
        };
        $clientManager = $this->createStub(ClientManagerInterface::class);
        $clientManager->method('getMessages')->willReturn([
            new Message([
                'data' => json_encode([
                    'event-type' => 'merged_members',
                    'event' => 'merged_members',
                ]),
                'attributes' => []
            ]),
            new Message([
                'data' => json_encode([]),
                'attributes' => [
                    'event-type' => 'any_event_type',
                ]
            ]),
            new Message([
                'data' => json_encode([]),
                'attributes' => [
                    'test_param' => 'param_value',
                ]
            ]),
            new Message([
                'data' => json_encode([]),
                'attributes' => [
                    'test_param' => [
                        'sub_test_param' => 'sub_test_value'
                    ]
                ]
            ])
        ]);
        $config = Config::create($config, $errorLogCallback);
        $dispatcher = $this->createMock(QueueingDispatcher::class);
        $dispatcher
            ->expects($this->exactly(10))
            ->method('dispatchToQueue')
            ->willReturnCallback(function (AbstractPubSubMessageHandlerJob $job) {
                $this->assertContains($job->queue, ['qn1', 'qn2', 'list_qn']);
                $this->assertContains($job->connection, ['con1', 'con2', 'list_con1']);
            })
        ;
        $service = new ListenerService($clientManager, $dispatcher, $config);
        $service->setErrorLogCallback($errorLogCallback);
        $service->run();

        $this->assertCount(0, $errors, 'There are errors: ' . print_r($errors, true));
    }

    /**
     * @dataProvider getCases
     * @return void
     */
    public function testFilterParam($messages, $handlers, $checker)
    {
        Debugger::spy();
        $config = [
            [
                'project_id' => 'p1',
                'subscription' => 's1',
                'queue_name' => 'list_qn',
                'connection_name' => 'list_con1',
                'handlers' => $handlers
            ]
        ];
        $clientManager = $this->createStub(ClientManagerInterface::class);
        $clientManager->method('getMessages')->willReturn($messages);
        $config = Config::create($config, function () {});
        $dispatcher = $this->createMock(QueueingDispatcher::class);
        $checker($dispatcher);
        $service = new ListenerService($clientManager, $dispatcher, $config);
        $service->run();
    }

    public function getCases(): array
    {
        return [
            [
                'messages' => [
                    new Message([
                        'data' => json_encode([]),
                        'attributes' => [
                            'test_param' => [
                                'sub_test_param' => 'sub_test_value'
                            ]
                        ]
                    ])
                ],
                'handlers' => [
                    [
                        'job_class' => TestJob2::class,
                        'filter' => [
                            'test_param.sub_test_param' => 'sub_test_value',
                        ]
                    ]
                ],
                'checker' => function (QueueingDispatcher|MockObject $dispatcher) {
                    $dispatcher
                        ->expects($this->exactly(1))
                        ->method('dispatchToQueue')
                    ;
                }
            ],
            [
                'messages' => [
                    new Message([
                        'data' => json_encode([]),
                        'attributes' => [
                            'test_param' => [
                                'sub_test_param' => 'sub_test_value'
                            ]
                        ]
                    ])
                ],
                'handlers' => [
                    [
                        'job_class' => TestJob2::class,
                        'filter' => [
                            'test_param.sub_test_param' => 'sub_test_value',
                        ]
                    ]
                ],
                'checker' => function (QueueingDispatcher|MockObject $dispatcher) {
                    $dispatcher
                        ->expects($this->exactly(1))
                        ->method('dispatchToQueue')
                    ;
                }
            ],
            [
                'messages' => [
                    new Message([
                        'data' => json_encode([
                            'test_param' => [
                                'sub_test_param' => [
                                    'sub_sub_test_param' => 'sub_sub_sub_value'
                                ]
                            ]
                        ]),
                        'attributes' => []
                    ])
                ],
                'handlers' => [
                    [
                        'job_class' => TestJob2::class,
                        'filter' => [
                            'test_param.sub_test_param.sub_sub_test_param' => 'sub_sub_sub_value',
                        ]
                    ]
                ],
                'checker' => function (QueueingDispatcher|MockObject $dispatcher) {
                    $dispatcher
                        ->expects($this->exactly(1))
                        ->method('dispatchToQueue')
                    ;
                }
            ],
            [
                'messages' => [
                    new Message([
                        'data' => json_encode([
                            'test_param' => [
                                'sub_test_param' => [
                                    'sub_sub_test_param' => 'sub_sub_sub_value'
                                ]
                            ]
                        ]),
                        'attributes' => [
                            'event_type' => 'event_type_example1',
                            'event' => 'event_example1'
                        ]
                    ])
                ],
                'handlers' => [
                    [
                        'job_class' => TestJob2::class,
                        'event_type' => 'event_type_example1',
                        'event' => 'event_example1',
                        'filter' => [
                            'test_param.sub_test_param.sub_sub_test_param' => 'sub_sub_sub_value',
                        ]
                    ]
                ],
                'checker' => function (QueueingDispatcher|MockObject $dispatcher) {
                    $dispatcher
                        ->expects($this->exactly(1))
                        ->method('dispatchToQueue')
                    ;
                }
            ],
            [
                'messages' => [
                    new Message([
                        'data' => json_encode([
                            'test_param' => [
                                'sub_test_param' => [
                                    'sub_sub_test_param' => 'sub_sub_sub_value'
                                ]
                            ]
                        ]),
                        'attributes' => [
                            'event_type' => 'event_type_example_bad',
                            'event' => 'event_example1'
                        ]
                    ])
                ],
                'handlers' => [
                    [
                        'job_class' => TestJob2::class,
                        'event_type' => 'event_type_example1',
                        'event' => 'event_example1',
                        'filter' => [
                            'test_param.sub_test_param.sub_sub_test_param' => 'sub_sub_sub_value',
                        ]
                    ]
                ],
                'checker' => function (QueueingDispatcher|MockObject $dispatcher) {
                    $dispatcher
                        ->expects($this->exactly(0))
                        ->method('dispatchToQueue')
                    ;
                }
            ],
            [
                'messages' => [
                    new Message([
                        'data' => json_encode([
                            'test_param' => [
                                'sub_test_param' => [
                                    'sub_sub_test_param' => 'sub_sub_sub_value'
                                ]
                            ]
                        ]),
                        'attributes' => [
                            'event_type' => 'event_type_example_1',
                            'event' => 'event_example_bad'
                        ]
                    ])
                ],
                'handlers' => [
                    [
                        'job_class' => TestJob2::class,
                        'event_type' => 'event_type_example1',
                        'event' => 'event_example1',
                        'filter' => [
                            'test_param.sub_test_param.sub_sub_test_param' => 'sub_sub_sub_value',
                        ]
                    ]
                ],
                'checker' => function (QueueingDispatcher|MockObject $dispatcher) {
                    $dispatcher
                        ->expects($this->exactly(0))
                        ->method('dispatchToQueue')
                    ;
                }
            ],
            [
                'messages' => [
                    new Message([
                        'data' => json_encode([
                            'test_param' => [
                                'sub_test_param' => [
                                    'sub_sub_test_param' => 'bad_value'
                                ]
                            ]
                        ]),
                        'attributes' => []
                    ])
                ],
                'handlers' => [
                    [
                        'job_class' => TestJob2::class,
                        'filter' => [
                            'test_param.sub_test_param.sub_sub_test_param' => 'sub_sub_sub_value',
                        ]
                    ]
                ],
                'checker' => function (QueueingDispatcher|MockObject $dispatcher) {
                    $dispatcher
                        ->expects($this->exactly(0))
                        ->method('dispatchToQueue')
                    ;
                }
            ]
        ];
    }
}
