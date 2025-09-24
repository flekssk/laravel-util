<?php

namespace FKS\Swagger;

use Illuminate\Support\ServiceProvider;
use FKS\Swagger\Processors\AttributesProcessor;

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
