<?php

namespace FKS\Providers;

use FKS\Services\Swagger\Processors\AttributesProcessor;
use Illuminate\Support\ServiceProvider;

class SwaggerProvider extends ServiceProvider
{
    public function register(): void
    {
        $documentations = 'l5-swagger.documentations';

        foreach (array_keys(config($documentations)) as $documentation) {
            config()->set(
                "l5-swagger.documentations.$documentation.scanOptions.processors",
                [
                    AttributesProcessor::class
                ]
            );
        }
    }
}
