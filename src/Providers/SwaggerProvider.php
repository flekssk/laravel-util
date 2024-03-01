<?php

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;
use FKS\Services\Swagger\Processors\FKSAttributesProcessor;

class SwaggerProvider extends ServiceProvider
{
    public function register(): void
    {
        config()->set(
            'l5-swagger.documentations.default.scanOptions.processors',
            [
                FKSAttributesProcessor::class
            ]
        );
    }
}
