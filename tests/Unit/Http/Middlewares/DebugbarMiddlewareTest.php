<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middlewares;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Testing\TestResponse;
use FKS\Middlewares\DebugbarMiddleware;
use Tests\CreatesApplication;

class DebugbarMiddlewareTest extends TestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        Env::enablePutenv();
        putenv('APP_RUNNING_IN_CONSOLE=false');
        parent::setUp();
    }

    public function testEnabledDebugbarWithParameter(): void
    {
        app()->instance('debugbar', Debugbar::getFacadeRoot());
        config()->set('debugbar.enabled', true);
        app()['env'] = 'staging';

        $request = new Request(request: ['debugbar' => true]);

        $middleware = new DebugbarMiddleware;
        $response = new TestResponse($middleware->handle($request, static fn () => new JsonResponse()));

        $response->assertJsonStructure(['_debugbar']);
    }

    public function testDisabledDebugbarWithParameter(): void
    {
        app()->instance('debugbar', Debugbar::getFacadeRoot());
        config()->set('debugbar.enabled', true);
        app()['env'] = 'staging';

        $request = new Request(request: ['debugbar' => true]);

        $middleware = new DebugbarMiddleware;
        $response = new TestResponse($middleware->handle($request, static fn () => new JsonResponse()));

        $response->assertJsonMissing(['_debugbar']);
    }

    public function testEnabledDebugbarWithoutParameter(): void
    {
        app()->instance('debugbar', Debugbar::getFacadeRoot());
        config()->set('debugbar.enabled', true);
        app()['env'] = 'staging';

        $request = new Request();

        $middleware = new DebugbarMiddleware;
        $response = new TestResponse($middleware->handle($request, static fn () => new JsonResponse()));

        $response->assertJsonMissing(['_debugbar']);
    }

    public function testDisabledDebugbarOnProduction(): void
    {
        app()->instance('debugbar', Debugbar::getFacadeRoot());
        config()->set('debugbar.enabled', true);
        app()['env'] = 'production';

        $request = new Request();

        $middleware = new DebugbarMiddleware;
        $response = new TestResponse($middleware->handle($request, static fn () => new JsonResponse()));

        $response->assertJsonMissing(['_debugbar']);
    }
}