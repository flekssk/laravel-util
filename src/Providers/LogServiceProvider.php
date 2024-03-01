<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;
use FKS\Contracts\Debugger;
use FKS\Contracts\FKSLogger;
use FKS\Logging\DebuggerService;
use FKS\Logging\FKSLoggerService;

class LogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind(Debugger::class, DebuggerService::class); //TODO MUST REMOVE IN THE FUTURE

        $this->app->bind(FKSLogger::class, FKSLoggerService::class);
    }
}
