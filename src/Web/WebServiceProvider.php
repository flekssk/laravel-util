<?php

namespace FKS\Web;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use FKS\Web\Http\Controllers\WebController;
use FKS\Web\Http\Middleware\Authorize;
use FKS\Web\OutputModifiers\OutputModifier;

class WebServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/web-view.php' => config_path('web-view.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../../resources/views' => base_path('resources/views/vendor/web-view'),
            ], 'views');

            $this->publishes([
                __DIR__.'/../../public' => public_path('vendor/web-view'),
            ], 'web-view-assets');
        }

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'web-view');

        $this->app->bind(OutputModifier::class, config('web-view.output_modifier'));

        Route::middlewareGroup('web-view', config('web-view.middleware', []));

        $this
            ->registerRoutes()
            ->registerWebGate();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/web-view.php', 'web-view');
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => 'web-view',
        ];
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            Route::get('/', [WebController::class, 'index']);
            Route::post('/', [WebController::class, 'execute']);
        });

        return $this;
    }

    protected function registerWebGate()
    {
        Gate::define('viewWeb', function ($user = null) {
            return app()->environment('local');
        });

        return $this;
    }
}
