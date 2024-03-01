<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;

class ActivityLogProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('FKS-activity-log.enable', false)) {
            $this->publishes([
                __DIR__ . '/../../database/migrations/activity_log' => database_path('migrations'),
            ]);

            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/activity_log');
        }
    }
}
