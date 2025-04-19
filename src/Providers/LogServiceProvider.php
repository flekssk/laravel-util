<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;
use FKS\Contracts\FKSLogger;
use FKS\Logging\LoggerService;

class LogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(FKSLogger::class, LoggerService::class);
    }
}
