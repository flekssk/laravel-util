<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use FKS\Services\Serializer\FKSSerializer;
use FKS\Services\Serializer\FKSSerializerInterface;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = new Application(
            __DIR__
        );

        $app->singleton(
            Kernel::class,
            Console\Kernel::class
        );

        $app->make(Kernel::class)->bootstrap();
        $app->bind(FKSSerializerInterface::class, FKSSerializer::class);

        config(['cache.default' => 'array']);

        return $app;
    }
}
