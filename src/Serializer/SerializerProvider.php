<?php

declare(strict_types=1);

namespace FKS\Serializer;

use Illuminate\Support\ServiceProvider;

class SerializerProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SerializerInterface::class, static function () {
            return new Serializer();
        });
    }
}
