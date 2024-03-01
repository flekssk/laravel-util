<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use FKS\Command\PubSub\ListenerCommand;
use FKS\Facades\FKSLogger;
use FKS\Services\PubSub\ClientManager;
use FKS\Services\PubSub\ListenerService;
use FKS\ValueObjects\PubSub\Config\Config;

class PubSubListenerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands(ListenerCommand::class,);
    }

    public function register()
    {
        $this->app->bind(ListenerService::class, function () {
            $errorLogCallback = static function($message) {
                FKSLogger::error($message->getMessage());
                Log::error($message);
            };
            $clientManager = $this->app->make(ClientManager::class);
            $dispatcher = $this->app->make(Dispatcher::class);
            $config = Config::create(config('pubsub.listeners') ?? [], $errorLogCallback);
            $service = new ListenerService($clientManager, $dispatcher, $config);
            $service->setErrorLogCallback($errorLogCallback);
            return $service;
        });
    }
}
