<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;
use FKS\Services\Serializer\SerializerInterface;
use FKS\Services\Serializer\SimpleSerializer;

class SerializerProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SerializerInterface::class, static function () {
            return new SimpleSerializer();
        });
    }
}
