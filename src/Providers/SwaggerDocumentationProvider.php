<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;
use L5Swagger\Generator;
use L5Swagger\GeneratorFactory;

class SwaggerDocumentationProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Generator::class, function ($app) {
            $documentation = config('l5-swagger.default');

            /** @var GeneratorFactory $factory */
            $factory = $app->make(GeneratorFactory::class);
            $generator = $factory->make($documentation);

            $generator->generateDocs();
            return $generator;
        });

    }
}