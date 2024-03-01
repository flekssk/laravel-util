<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\ServiceProvider;
use FKS\Services\Serializer\FKSSerializerInterface;
use FKS\Services\Serializer\FKSSimpleSerializer;

class SerializerProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FKSSerializerInterface::class, static function () {
            return new FKSSimpleSerializer();
        });
    }
}
