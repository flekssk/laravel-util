<?php

namespace FKS\Providers;

use FKS\Services\Swagger\Processors\AttributesProcessor;
use Illuminate\Support\ServiceProvider;

class SwaggerProvider extends ServiceProvider
{
    public function register(): void
    {
        config()->set(
            'l5-swagger.documentations.default.scanOptions.processors',
            [
                AttributesProcessor::class
            ]
        );
    }
}
