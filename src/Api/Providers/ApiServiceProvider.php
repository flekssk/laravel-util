<?php

declare(strict_types=1);

namespace FKS\Api\Providers;

use FKS\Api\ApiClient;
use FKS\Api\Enrichers\RequestMiddlewareInterface;
use FKS\Api\ValueObjects\ApiClientConfig;
use FKS\Serializer\SerializerFacade;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach (config('api', []) as $item) {
            /** @var ApiClientConfig $config */
            $config = SerializerFacade::deserializeFromArray($item, ApiClientConfig::class);
            
            if (!class_exists($config->apiClient)) {
                throw new \Exception('Class not found');
            }

            $this->app->singleton($config->apiClient, function () use ($config) {
                return new $config->apiClient(
                    new Client([
                        'base_uri' => $config->baseUrl,
                    ]),
                    $config
                );
            });
        }

    }
}