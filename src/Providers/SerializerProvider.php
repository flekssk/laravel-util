<?php

declare(strict_types=1);

namespace FKS\Providers;

use FKS\Services\Serializer\Serializer;
use Illuminate\Support\ServiceProvider;
use FKS\Services\Serializer\SerializerInterface;

class SerializerProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SerializerInterface::class, static function () {
            return new Serializer();
        });
    }
}
